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
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchValidateHandler
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
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchJobQueueInterface   $batchJobQueue
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchJobQueueInterface $batchJobQueue,
        JobQueueInterface $jobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchJobQueue   = $batchJobQueue;
        $this->jobQueue        = $jobQueue;
    }

    /**
     * @param BatchValidate $batchValidate
     *
     * @return BatchResult
     */
    public function handle(BatchValidate $batchValidate)
    {
        // Get unvalidated sheets
        $sheets = $this->sheetRepository->getUnvalidatedSheetsById($batchValidate->ids);

        $this->sheetRepository->updateStateBySheetsId($batchValidate->ids, Sheet::STATE_VALIDATED);

        if (!empty($batchValidate->ids)) {
            // reindex sheet in elasticsearch
            $this->jobQueue->indexSheets($batchValidate->ids);

            // send email
            $this->jobQueue->sendEmailing($batchValidate->event, $batchValidate->ids, Events::SHEET_VALIDATED, true);
            
            $this->batchJobQueue->createJob(
                $batchValidate->ids,
                $batchValidate->admin,
                ['comment' => $batchValidate->comment]
            );
        }

        return new BatchResult(count($sheets), $batchValidate->getMessage() . 'validate.success');
    }
}
