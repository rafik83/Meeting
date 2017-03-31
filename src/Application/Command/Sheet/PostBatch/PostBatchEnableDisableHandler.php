<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Command\Sheet\BatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
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
     * PostBatchEnableDisableHandler constructor.
     *
     * @param BatchCatalogHandler      $batchCatalogHandler
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     * @param EnableDisableManager     $enableDisableManager
     */
    public function __construct(
        BatchCatalogHandler $batchCatalogHandler,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        EnableDisableManager $enableDisableManager
    ) {
        $this->batchCatalogHandler  = $batchCatalogHandler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->dateTime             = $dateTime;
        $this->enableDisableManager = $enableDisableManager;
    }

    /**
     * @param PostBatchEnableDisable $command
     */
    public function handle(PostBatchEnableDisable $command)
    {
        // remove sheet from catalog if sheet is disable
        if ($command->state === false) {
            $this->batchCatalogHandler->handle(new BatchCatalog(
                $command->ids,
                $command->state,
                $command->admin
            ));
        }

        foreach ($command->sheets as $sheet) {
            $this->enableDisableManager->update($sheet, $command->state);

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime,
                    $command->state
                )
            );
        }
    }
}
