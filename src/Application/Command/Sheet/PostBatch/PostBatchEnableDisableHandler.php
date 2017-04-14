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
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchEnableDisableHandler
{
    /**
     * @var BatchCatalogHandler
     */
    private $batchCatalogHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var EnableDisableManager
     */
    private $enableDisableManager;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * PostBatchEnableDisableHandler constructor.
     *
     * @param BatchCatalogHandler      $batchCatalogHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     * @param EnableDisableManager     $enableDisableManager
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        BatchCatalogHandler $batchCatalogHandler,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        EnableDisableManager $enableDisableManager,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->batchCatalogHandler  = $batchCatalogHandler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->dateTime             = $dateTime;
        $this->enableDisableManager = $enableDisableManager;
        $this->sheetIndexer         = $sheetIndexer;
    }

    /**
     * @param PostBatchEnableDisable $command
     */
    public function handle(PostBatchEnableDisable $command)
    {
        $state = $command->state === BatchEnableDisableHandler::STATE_ENABLE;

        $this->sheetIndexer->updateSheets($command->sheets);

        // remove sheet from catalog if sheet is disable
        if ($command->state === BatchEnableDisableHandler::STATE_DISABLE) {
            $this->batchCatalogHandler->handle(new BatchCatalog(
                $command->ids,
                BatchCatalogHandler::REMOVE_CATALOG,
                $command->admin
            ));
        }

        foreach ($command->sheets as $sheet) {
            $this->enableDisableManager->update($sheet, $state);

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime,
                    $state
                )
            );
        }
    }
}
