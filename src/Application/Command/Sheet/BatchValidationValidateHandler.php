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

class BatchValidationValidateHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var BatchJobQueueInterface
     */
    private $batchJobQueue;

    /**
     * BatchValidationValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param \DateTimeInterface       $datetime
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $datetime,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->datetime        = $datetime;
        $this->batchJobQueue   = $batchJobQueue;
    }

    /**
     * @param BatchValidationValidate $batch
     *
     * @return BatchResult
     */
    public function handle(BatchValidationValidate $batch)
    {
        $sheets = $this->sheetRepository->getSheetsById($batch->ids);

        $this->sheetRepository->updateValidationState(
            $batch->ids,
            Sheet::STATE_VALIDATION_VALIDATED
        );

        if (!empty($batch->ids)) {
            $this->batchJobQueue->createJob($batch->ids, $batch->admin);
        }

        return new BatchResult(count($sheets), $batch->getMessage() . 'validation.validate.success');
    }
}
