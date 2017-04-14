<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchDraftHandler
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
     * BatchPendingHandler constructor.
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
     * @param BatchDraft $batchPending
     *
     * @return BatchResult
     */
    public function handle(BatchDraft $batchPending)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchPending->ids);

        $this->sheetRepository->updateValidationState(
            $batchPending->ids,
            Sheet::STATE_VALIDATION_DRAFT
        );

        if (!empty($batchPending->ids)) {
            $this->batchJobQueue->createJob(
                $batchPending->ids,
                $batchPending->admin
            );
        }

        return new BatchResult(count($sheets), $batchPending->getMessage() . 'draft.success');
    }
}
