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
use Proximum\Vimeet\Application\Event\Sheet\SheetDraftEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchDraftHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * PostBatchValidationDraftHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param PostBatchDraft $command
     */
    public function handle(PostBatchDraft $command)
    {
        foreach ($command->sheets as $sheet) {
            if (!$sheet->isValidationDraft()) {

                $this->eventDispatcher->dispatch(
                    Events::SHEET_VALIDATION_DRAFT,
                    new SheetDraftEvent(
                        $sheet,
                        $command->admin,
                        $this->datetime
                    )
                );
            }
        }
    }
}
