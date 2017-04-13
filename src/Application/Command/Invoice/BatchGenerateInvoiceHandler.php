<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvoicedEvent;
use Proximum\Vimeet\Application\View\Sheet\SheetInvoicedView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BatchGenerateInvoiceHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var CreateHandler
     */
    private $createHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param CreateHandler            $createHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CreateHandler $createHandler,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->createHandler   = $createHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->sheetIds);

        if (count($sheets) === 0) {
            return;
        }

        // get event prefix
        $firstSheet = reset($sheets);

        if (false === $firstSheet) {
            return;
        }

        $event  = $firstSheet->getEvent();
        $prefix = $event->getInvoicePrefix();

        $sheetInvoicedViews = [];

        foreach ($sheets as $sheet) {
            if ($event === $sheet->getEvent()) {
                $invoices = $this->createHandler->handle(new Create($sheet, $prefix));

                if (!empty($invoices)) {
                    $sheetInvoicedViews[] = new SheetInvoicedView($sheet, $invoices);
                }
            }
        }

        if (!empty($sheetInvoicedViews)) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_INVOICED,
                new SheetInvoicedEvent(
                    $batchGenerateInvoice->admin,
                    $event,
                    $this->datetime,
                    $sheetInvoicedViews
                )
            );
        }
    }
}
