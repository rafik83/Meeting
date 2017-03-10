<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
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
     * @param SheetRepositoryInterface  $sheetRepository
     * @param CreateHandler             $createHandler
     * @param DelayedEventDispatcher    $eventDispatcher
     * @param \DateTimeInterface        $datetime
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        CreateHandler              $createHandler,
        DelayedEventDispatcher     $eventDispatcher,
        \DateTimeInterface         $datetime
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->createHandler     = $createHandler;
        $this->eventDispatcher   = $eventDispatcher;
        $this->datetime          = $datetime;
    }
    
    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     *
     * @return BatchResult
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);
        
        if (count($sheets) === 0) {
            return $this->getBatchResult($batchGenerateInvoice, 0);
        }

        // get event prefix
        $firstSheet = reset($sheets);
        $event      = $firstSheet->getEvent();
        $prefix     = $event->getInvoicePrefix();

        $sheetsInvoiced = [];

        foreach ($sheets as $sheet) {
            if (true === $this->createHandler->handle(new Create($sheet, $prefix))) {
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
        
        return $this->getBatchResult($batchGenerateInvoice, count($sheetsInvoiced));
    }
    
    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     * @param $count
     *
     * @return BatchResult
     */
    private function getBatchResult(BatchGenerateInvoice $batchGenerateInvoice, $count)
    {
        return new BatchResult($count, $batchGenerateInvoice->getMessage() . 'generateInvoice.success');
    }
}
