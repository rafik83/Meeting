<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQuery;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQueryHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;

class ExportHandler
{
    /**
     * @var SerializerAdapterInterface
     */
    private $serializer;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /** @var BillingInfosQueryHandler */
    private $billingInfosQueryHandler;

    /**
     * Export constructor.
     *
     * @param SerializerAdapterInterface $serializer
     * @param EventRepositoryInterface   $eventRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param BillingInfosQueryHandler   $billingInfosQueryHandler
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        EventRepositoryInterface $eventRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        BillingInfosQueryHandler $billingInfosQueryHandler
    ) {
        $this->serializer               = $serializer;
        $this->eventRepository          = $eventRepository;
        $this->invoiceRepository        = $invoiceRepository;
        $this->billingInfosQueryHandler = $billingInfosQueryHandler;
    }

    /**
     * @param Export $command
     *
     * @return InvoicesNormalizerView
     */
    public function handle(Export $command)
    {
        $endDate = new \DateTime(sprintf('%s %s', $command->endDate->format('Y-m-d'), '23:59:59.999'));
        $events  = $this->eventRepository->getEventsByAdmin($command->admin);

        $dateFormatters = [];

        foreach ($events as $event) {
            $dateFormatters[$event->getId()] = IntlDateFormatter::create(
                $command->admin->getLocale(),
                IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE,
                $event->getTimeZone()
            );
        }

        $invoices = $this->invoiceRepository->getFilteredByEvents($events, $command->beginDate, $endDate);
        $invoiceExportViews = [];
        $billingInfosViewOfSheets = [];

        foreach ($invoices as $invoice) {
            // store the billingInfo of the sheet in an array to avoid potentially
            // recreate it if there is multiple invoices for a sheet
            if (!isset($billingInfosViewOfSheets[$invoice->getSheet()->getId()])) {
                $billingInfosViewOfSheets[$invoice->getSheet()->getId()] = $this
                    ->billingInfosQueryHandler
                    ->handle(new BillingInfosQuery($invoice->getSheet()));
            }

            $invoiceExportViews[] = $this->serializer->deserialize(
                $invoice->getData(),
                ExportView::class,
                'json',
                [
                    'invoice'                 => $invoice,
                    'locale'                  => $command->admin->getLocale(),
                    'dateFormatter'           => $dateFormatters[$invoice->getEvent()->getId()],
                    'billingInfosViewOfSheet' => $billingInfosViewOfSheets[$invoice->getSheet()->getId()],
                ]
            );
        }

        return new InvoicesNormalizerView($invoiceExportViews, $command->admin->getLocale());
    }
}
