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
     * @var ValidateHandler
     */
    private $validateHandler;

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
     * @param ValidateHandler          $validateHandler
     * @param \DateTimeInterface       $datetime
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ValidateHandler $validateHandler,
        \DateTimeInterface $datetime,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->validateHandler = $validateHandler;
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

        // Validate sheets
        /** @var Sheet $sheet */
        foreach ($sheets as $sheet) {
            $this->validateHandler->handle(new Validate($sheet, $batchValidate->admin, $this->datetime, $batchValidate->comment));
        }

        $this->batchJobQueue->createJob(
            $batchValidate->ids,
            $batchValidate->admin,
            ['comment' => $batchValidate->comment]
        );

        return new BatchResult(count($sheets), $batchValidate->getMessage() . 'validate.success');
    }
}
