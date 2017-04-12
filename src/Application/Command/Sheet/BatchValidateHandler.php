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

class BatchValidateHandler
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
     * BatchValidateHandler constructor.
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
            $this->batchJobQueue->createJob(
                $batchValidate->ids,
                $batchValidate->admin,
                ['comment' => $batchValidate->comment]
            );
        }

        return new BatchResult(count($sheets), $batchValidate->getMessage() . 'validate.success');
    }
}
