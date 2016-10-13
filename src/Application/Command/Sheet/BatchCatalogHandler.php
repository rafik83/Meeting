<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
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
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
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
                        $this->datetime,
                        $command->state
                    )
                );
            }

            $sheet->setInCatalog($command->state);

            if ($command->state === true) {
                $sheet->setInCatalogAt($this->datetime);
            }

            $this->sheetRepository->set($sheet);
        }

        $message = ($command->state) ? 'catalog.add.success' : 'catalog.remove.success';

        return new BatchResult(count($sheets), $command->getMessage() . $message);
    }
}
