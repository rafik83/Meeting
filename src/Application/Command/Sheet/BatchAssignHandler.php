<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * BatchAssignHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param BatchAssign $batchAssign
     *
     * @return BatchResult
     */
    public function handle(BatchAssign $batchAssign)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchAssign->ids);

        // Assign admin to sheets
        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->assign($batchAssign->admin));
        }

        return new BatchResult(count($sheets), $batchAssign->getMessage() . 'assign.success');
    }
}
