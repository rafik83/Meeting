<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAcceptHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var BatchJobQueueInterface
     */
    private $batchJobQueue;

    /**
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchJobQueue   = $batchJobQueue;
    }

    /**
     * @param BatchAccept $batchAccept
     *
     * @return BatchResult
     */
    public function handle(BatchAccept $batchAccept)
    {
        // Get sheets unaccepted by id
        $sheets = $this->sheetRepository->getSheetsUnacceptedById($batchAccept->ids);

        if (!empty($batchAccept->ids)) {
            $this->sheetRepository->updateStateBySheetsId(
                $batchAccept->ids,
                Sheet::STATE_ACCEPTED
            );

            $this->batchJobQueue->createJob($batchAccept->ids, $batchAccept->admin);
        }

        return new BatchResult($sheets, $batchAccept->getMessage() . 'accept.success');
    }
}
