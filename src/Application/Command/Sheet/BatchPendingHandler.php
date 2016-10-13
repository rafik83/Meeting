<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPendingHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * BatchPendingHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param BatchPending $batchPending
     *
     * @return BatchResult
     */
    public function handle(BatchPending $batchPending)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchPending->ids);

        foreach ($sheets as $sheet) {
            $sheet->setState(Sheet::STATE_PENDING);

            $this->sheetRepository->set($sheet);
        }

        return new BatchResult(count($sheets), $batchPending->getMessage() . 'pending.success');
    }
}
