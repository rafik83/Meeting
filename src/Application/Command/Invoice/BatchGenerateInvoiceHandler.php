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
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

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
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param CreateHandler            $createHandler
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CreateHandler $createHandler,
        DelayedEventDispatcher $eventDispatcher,
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
        $event      = $firstSheet->getEvent();
        $prefix     = $event->getInvoicePrefix();

        $sheetsInvoiced = [];

        foreach ($sheets as $sheet) {
            if ($event === $sheet->getEvent() && true === $this->createHandler->handle(new Create($sheet, $prefix))) {
                $sheetsInvoiced[] = $sheet;
            }
        }

        if (!empty($sheetsInvoiced)) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_INVOICED,
                new SheetInvoicedEvent(
                    $batchGenerateInvoice->admin,
                    $event,
                    $this->datetime,
                    $sheetsInvoiced
                )
            );
        }
    }
}
