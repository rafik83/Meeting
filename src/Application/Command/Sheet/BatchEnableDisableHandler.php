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
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchEnableDisableHandler
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
     * @var BatchCatalogHandler
     */
    private $batchCatalogHandler;

    /**
     * BatchEnableDisableHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param BatchCatalogHandler      $batchCatalogHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        BatchCatalogHandler $batchCatalogHandler
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->eventDispatcher     = $eventDispatcher;
        $this->batchCatalogHandler = $batchCatalogHandler;
    }

    /**
     * @param BatchEnableDisable $batchEnableDisable
     *
     * @return BatchResult
     */
    public function handle(BatchEnableDisable $batchEnableDisable)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchEnableDisable->ids);

        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setEnable($batchEnableDisable->state));

            // remove sheet from catalog if sheet is disable
            if ($batchEnableDisable->state === false) {
                $this->batchCatalogHandler->handle(new BatchCatalog(
                    $batchEnableDisable->ids,
                    $batchEnableDisable->date,
                    $batchEnableDisable->state,
                    $batchEnableDisable->admin
                ));
            }

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $batchEnableDisable->admin,
                    new \DateTime(),
                    $batchEnableDisable->state
                )
            );
        }

        return new BatchResult(count($sheets));
    }
}
