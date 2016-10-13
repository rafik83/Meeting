<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetPendingEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchPendingHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchPendingHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param BatchPending $batchPending
     *
     * @return BatchResult
     */
    public function handle(BatchPending $batchPending)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchPending->ids);

        foreach ($sheets as $sheet) {
            if (!$sheet->isPending()) {
                $sheet->setState(Sheet::STATE_PENDING);
                $this->sheetRepository->set($sheet);

                $this->eventDispatcher->dispatch(
                    Events::SHEET_PENDING,
                    new SheetPendingEvent(
                        $sheet,
                        $batchPending->admin,
                        $this->datetime
                    )
                );
            }
        }

        return new BatchResult(count($sheets), $batchPending->getMessage() . 'pending.success');
    }
}
