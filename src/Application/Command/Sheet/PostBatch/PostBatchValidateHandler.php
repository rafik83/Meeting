<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchValidateHandler
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
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
        $this->sheetIndexer    = $sheetIndexer;
    }

    /**
     * @param PostBatchValidate $command
     */
    public function handle(PostBatchValidate $command)
    {
        $this->sheetIndexer->updateSheets($command->sheets);

        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_VALIDATED,
                new SheetValidatedEvent(
                    $sheet,
                    $this->dateTime,
                    $command->comment,
                    $command->admin
                )
            );
        }
    }
}
