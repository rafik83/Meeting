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

class BatchEnableDisableHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * BatchEnableDisableHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
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

        foreach($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setEnable($batchEnableDisable->state));
        }

        return new BatchResult(count($sheets));
    }
}
