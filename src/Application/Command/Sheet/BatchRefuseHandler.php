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

    /** @var BatchRefuseJobQueue */
    private $batchRefuseJobQueue;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchRefuseJobQueue      $batchRefuseJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchRefuseJobQueue $batchRefuseJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchRefuseJobQueue = $batchRefuseJobQueue;
    }

    public function handle(BatchRefuse $batchRefuse)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchRefuse->ids);

        if (!empty($batchRefuse->ids)) {
            $this->sheetRepository->updateStateBySheetsId(
                $batchRefuse->ids,
                Sheet::STATE_REFUSED
            );

            $this->batchRefuseJobQueue->createJob($batchRefuse->ids, $batchRefuse->admin);
        }

        return new BatchResult(count($sheets), $batchRefuse->getMessage() . 'refuse.success');
    }
}
