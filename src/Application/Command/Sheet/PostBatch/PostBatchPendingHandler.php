<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetPendingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchPendingHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, \DateTimeInterface $dateTime)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param PostBatchPending $command
     */
    public function handle(PostBatchPending $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_PENDING,
                new SheetPendingEvent(
                    $sheet,
                    $this->dateTime,
                    $command->admin
                )
            );
        }
    }
}
