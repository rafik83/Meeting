<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQuery;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQueryHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvoicedEvent;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Invoice\SheetInvoicedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetInvoicedEventSubscriber implements EventSubscriberInterface
{
    /** @var InvoiceUrlViewQueryHandler */
    private $invoiceUrlViewQueryHandler;

    /** @var MailerInterface */
    private $mailer;

    /** @var EventSender */
    private $sender;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /**
     * @param InvoiceUrlViewQueryHandler     $invoiceUrlViewQueryHandler
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param MailerInterface                $mailer
     * @param EventSender                    $sender
     * @param EventUrlGeneratorInterface     $eventUrlGenerator
     */
    public function __construct(
        InvoiceUrlViewQueryHandler $invoiceUrlViewQueryHandler,
        BillingInfoRepositoryInterface $billingInfoRepository,
        MailerInterface $mailer,
        EventSender $sender,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->invoiceUrlViewQueryHandler = $invoiceUrlViewQueryHandler;
        $this->mailer                     = $mailer;
        $this->sender                     = $sender;
        $this->eventUrlGenerator          = $eventUrlGenerator;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param SheetInvoicedEvent $sheetInvoicedEvent
     */
    public function onSheetInvoiced(SheetInvoicedEvent $sheetInvoicedEvent)
    {
        $billingInfosIndexedBySheetId = $this->getBillingInfosIndexedBySheetId($sheetInvoicedEvent->getSheets());

        foreach ($sheetInvoicedEvent->getSheetInvoicedViews() as $sheetInvoicedView) {
            $sheet = $sheetInvoicedView->sheet;

            $mail = new SheetInvoicedMail(
                $sheet,
                $this->sender->generate($sheet->getEvent()),
                [
                    $sheet->getOwner()->getEmail(),
                    isset($billingInfosIndexedBySheetId[$sheet->getId()])
                        ? $billingInfosIndexedBySheetId[$sheet->getId()]->getEmail()
                        : null
                ],
                new ParticipantInfoView($sheet->getOwner()->getFirstName(), $sheet->getOwner()->getLastName()),
                $sheet->getOwnerLocale(),
                array_map(
                    function (Invoice $invoice) {
                        return $this->invoiceUrlViewQueryHandler->handle(new InvoiceUrlViewQuery($invoice));
                    },
                    $sheetInvoicedView->invoices
                ),
                $this->eventUrlGenerator->generateEventAbsoluteUrl(
                    $sheet->getEvent(),
                    'event_order_list',
                    [
                        '_locale' => $sheet->getOwnerLocale(), 'sheet' => $sheet->getId(),
                    ]
                )
            );

            $this->mailer->send($mail);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_INVOICED => 'onSheetInvoiced',
        ];
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return BillingInfo[] indexed by Sheet id
     */
    private function getBillingInfosIndexedBySheetId($sheets)
    {
        $billingInfos = $this->billingInfoRepository->getBySheets($sheets);
        $billingInfosIndexedBySheetId = [];

        foreach ($billingInfos as $billingInfo) {
            $billingInfosIndexedBySheetId[$billingInfo->getSheet()->getId()] = $billingInfo;
        }

        return $billingInfosIndexedBySheetId;
    }
}
