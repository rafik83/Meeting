<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class BatchGenerateInvoiceHandler
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
     * @param BatchGenerateInvoice $batchGenerateInvoice
     *
     * @return BatchResult
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $this->jobQueue->generateInvoice($batchGenerateInvoice->event, $batchGenerateInvoice->ids, $batchGenerateInvoice->admin);

        return new BatchResult(
            count($batchGenerateInvoice->ids),
            $batchGenerateInvoice->getMessage() . 'generateInvoiceBatch.success'
        );
    }
}
