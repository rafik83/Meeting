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
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchCatalogHandler
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
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, DelayedEventDispatcher $eventDispatcher)
    {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param BatchCatalog $command
     *
     * @return BatchResult
     */
    public function handle(BatchCatalog $command)
    {
        $sheets = $this->sheetRepository->getSheetsById($command->ids);

        foreach ($sheets as $sheet) {
            // trace state in catalog change only
            if ($sheet->isInCatalog() !== $command->state) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_CATALOG,
                    new SheetCatalogEvent(
                        $sheet,
                        $command->admin,
                        new \DateTime(),
                        $command->state
                    )
                );
            }

            $sheet->setInCatalog($command->state);

            if ($command->state === true) {
                $sheet->setInCatalogAt($command->date);
            }

            $this->sheetRepository->set($sheet);
        }

        $message = ($command->state) ? 'catalog.add.success' : 'catalog.remove.success';

        return new BatchResult(count($sheets), $command->getMessage() . $message);
    }
}
