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

class BatchCatalogHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
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
            $sheet->setInCatalog($command->state);

            if ($command->state === true) {
                $sheet->setInCatalogAt($command->date);
            }

            $this->sheetRepository->set($sheet);
        }

        return new BatchResult(count($sheets));
    }
}
