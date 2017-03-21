<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;
use IntlDateFormatter;

class InvoiceExportQueryHandler
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

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * InvoiceExportQueryHandler constructor.
     *
     * @param SerializerAdapterInterface $serializer
     * @param EventRepositoryInterface   $eventRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param Balance                    $balance
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        EventRepositoryInterface $eventRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        Balance $balance
    ) {
        $this->serializer        = $serializer;
        $this->eventRepository   = $eventRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->balance           = $balance;
    }

    /**
     * @param InvoiceExportQuery $query
     *
     * @return InvoicesNormalizerView
     */
    public function handle(InvoiceExportQuery $query)
    {
        $invoiceExportViews = [];

        $events = $this->eventRepository->getEventsByAdmin($query->user);

        foreach ($events as $event) {
            $invoices = $this->invoiceRepository->getAllByEvent($event);

            foreach ($invoices as $invoice) {
                $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle(
                    $invoice->getSheet(),
                    $invoice->getEvent()->getAvailableLocale($query->user->getLocale())
                );

                $dateFormatter = IntlDateFormatter::create(
                    $query->user->getLocale(),
                    IntlDateFormatter::SHORT,
                    IntlDateFormatter::NONE,
                    $event->getTimeZone()
                );

                $invoiceExportView = new ExportView(
                    $invoice->getEvent()->getId(),
                    $invoice->getEvent()->getTitle(),
                    $invoice->getSheet()->getOwner()->getId(),
                    $sheetTitle,
                    $invoice->getNumber(),
                    $dateFormatter->format($invoice->getCreatedAt()),
                    $invoice->getTotal(),
                    $invoice->getTotalWithVat(),
                    $invoice->getVatAmount(),
                    $this->balance->getBalance($invoice->getSheet()),
                    $invoice->getEvent()->getConfiguration()->getAnalyticsCode()
                );

                $invoiceExportViews[] = $this->serializer->deserialize(
                    $invoice->getData(),
                    ExportView::class,
                    'json',
                    ['invoice' => $invoiceExportView]
                );
            }
        }

        return new InvoicesNormalizerView($invoiceExportViews, $query->user->getLocale());
    }
}
