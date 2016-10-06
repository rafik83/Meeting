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
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchEnableDisableHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param BatchCatalogHandler      $batchCatalogHandler
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        BatchCatalogHandler $batchCatalogHandler,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->eventDispatcher     = $eventDispatcher;
        $this->batchCatalogHandler = $batchCatalogHandler;
        $this->datetime            = $datetime;
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
                    $this->datetime,
                    $batchEnableDisable->state,
                    $batchEnableDisable->admin
                ));
            }

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $batchEnableDisable->admin,
                    $this->datetime,
                    $batchEnableDisable->state
                )
            );
        }

        $message = ($batchEnableDisable->state === true) ? 'enable.success' : 'disable.success';

        return new BatchResult(count($sheets), $batchEnableDisable->getMessage() . $message);
    }
}
