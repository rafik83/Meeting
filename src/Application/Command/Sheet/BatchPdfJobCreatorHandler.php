<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class BatchPdfJobCreatorHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param JobQueueInterface $jobQueue
     */
    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param BatchPdfJobCreator $batchPdfJobCreator
     *
     * @return BatchResult
     */
    public function handle(BatchPdfJobCreator $batchPdfJobCreator): BatchResult
    {
        $this->jobQueue->printSheetsPdf(
            $batchPdfJobCreator->event,
            $batchPdfJobCreator->sheetIds,
            $batchPdfJobCreator->emailToNotify,
            $batchPdfJobCreator->locale,
            $batchPdfJobCreator->orderBy
        );

        return new BatchResult(
            count($batchPdfJobCreator->sheetIds),
            $batchPdfJobCreator->getMessage() . 'printPdf.success'
        );
    }
}
