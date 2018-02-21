<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class IndexHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->sheetIndexer = $sheetIndexer;
    }

    /**
     * @param Index $command
     */
    public function handle(Index $command): void
    {
        $sheets = $this->sheetRepository->getByEvent($command->event);

        $this->sheetIndexer->reindexSheets($sheets);
    }
}
