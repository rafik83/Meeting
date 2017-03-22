<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;
use IntlDateFormatter;

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

    /**
     * Export constructor.
     *
     * @param SerializerAdapterInterface $serializer
     * @param EventRepositoryInterface   $eventRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        EventRepositoryInterface $eventRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->serializer        = $serializer;
        $this->eventRepository   = $eventRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param Export $command
     *
     * @return InvoicesNormalizerView
     */
    public function handle(Export $command)
    {
        $invoiceExportViews = [];
        $dateFormatters     = [];
        $events   = $this->eventRepository->getEventsByAdmin($command->admin);

        foreach ($events as $event) {
            $dateFormatters[$event->getId()] = IntlDateFormatter::create(
                $command->admin->getLocale(),
                IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE,
                $event->getTimeZone()
            );
        }

        $invoices = $this->invoiceRepository->getFilteredByEvents($events, $command->beginDate, $command->endDate);

        foreach ($invoices as $invoice) {
            $invoiceExportViews[] = $this->serializer->deserialize(
                $invoice->getData(),
                ExportView::class,
                'json',
                ['invoice' => $invoice, 'locale' => $command->admin->getLocale(), 'dateFormatters' => $dateFormatters]
            );
        }

        return new InvoicesNormalizerView($invoiceExportViews, $command->admin->getLocale());
    }
}
