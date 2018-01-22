<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchRefuseJobQueue;

class BatchRefuseHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var BatchEnableDisableHandler */
    private $batchEnableDisableHandler;

    /** @var BatchRefuseJobQueue */
    private $batchRefuseJobQueue;

    /**
     * @param SheetRepositoryInterface  $sheetRepository
     * @param BatchEnableDisableHandler $batchEnableDisableHandler
     * @param BatchRefuseJobQueue       $batchRefuseJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchEnableDisableHandler $batchEnableDisableHandler,
        BatchRefuseJobQueue $batchRefuseJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchRefuseJobQueue = $batchRefuseJobQueue;
        $this->batchEnableDisableHandler = $batchEnableDisableHandler;
    }

    public function handle(BatchRefuse $batchRefuse)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchRefuse->ids);

        $batchDisable = new BatchEnableDisable($batchRefuse->ids, false, $batchRefuse->admin);
        $batchDisableResult = $this->batchEnableDisableHandler->handle($batchDisable);

        $disabledSheetsId = array_map(
            function (Sheet $sheet) {
                return $sheet->getId();
            },
            $batchDisableResult->sheets
        );

        if (!empty($disabledSheetsId)) {
            $this->sheetRepository->updateStateBySheetsId($disabledSheetsId, Sheet::STATE_REFUSED);
            $this->batchRefuseJobQueue->createJob($disabledSheetsId, $batchRefuse->admin);
        }

        if (!empty($batchDisableResult->ignoredSheetsMessage)) {
            return new BatchResult(
                $batchDisableResult->sheets,
                $batchRefuse->getMessage() . 'refuse.warning',
                $batchDisableResult->ignoredSheetsMessage
            );
        }

        return new BatchResult(
            $sheets,
            $batchRefuse->getMessage() . 'refuse.success'
        );
    }
}
