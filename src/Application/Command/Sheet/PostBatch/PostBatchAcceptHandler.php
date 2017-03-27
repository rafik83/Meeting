<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchAcceptHandler
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
     * PostBatchAcceptHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param PostBatchAccept $command
     */
    public function handle(PostBatchAccept $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_ACCEPTED,
                new SheetAcceptedEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime
                )
            );
        }
    }
}
