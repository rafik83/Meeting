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
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchCatalogHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param EnableDisableManager     $enableDisableManager
     * @param \DateTimeInterface       $datetime
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EnableDisableManager $enableDisableManager,
        \DateTimeInterface $datetime,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->eventDispatcher      = $eventDispatcher;
        $this->datetime             = $datetime;
        $this->enableDisableManager = $enableDisableManager;
        $this->sheetIndexer         = $sheetIndexer;
    }

    /**
     * @param PostBatchCatalog $command
     */
    public function handle(PostBatchCatalog $command)
    {
        $this->sheetIndexer->updateSheets($command->sheets);

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
