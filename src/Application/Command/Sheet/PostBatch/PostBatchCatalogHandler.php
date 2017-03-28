<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchCatalogHandler
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
     * @var EnableDisableManager
     */
    private $enableDisableManager;

    /**
     * PostBatchCatalogHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EnableDisableManager     $enableDisableManager
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EnableDisableManager $enableDisableManager,
        \DateTimeInterface $datetime
    ) {
        $this->eventDispatcher      = $eventDispatcher;
        $this->datetime             = $datetime;
        $this->enableDisableManager = $enableDisableManager;
    }

    /**
     * @param PostBatchCatalog $command
     */
    public function handle(PostBatchCatalog $command)
    {
        foreach ($command->sheets as $sheet) {

            $this->enableDisableManager->update($sheet, $command->state);

            // trace state in catalog change only
            if ($sheet->isInCatalog() !== $command->state) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_CATALOG,
                    new SheetCatalogEvent(
                        $sheet,
                        $command->admin,
                        $this->datetime,
                        $command->state
                    )
                );
            }
        }
    }
}
