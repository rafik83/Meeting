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
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAcceptHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var AcceptHandler
     */
    private $acceptHandler;

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
     * @param AcceptHandler            $acceptHandler
     * @param \DateTimeInterface       $datetime
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AcceptHandler $acceptHandler,
        \DateTimeInterface $datetime,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->acceptHandler   = $acceptHandler;
        $this->datetime        = $datetime;
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

        // Accept sheets
        foreach ($sheets as $sheet) {
            $this->acceptHandler->handle(new Accept($sheet, $batchAccept->admin, $this->datetime));
        }

        $this->batchJobQueue->createJob($batchAccept->ids, $batchAccept->admin, null);

        return new BatchResult(count($sheets), $batchAccept->getMessage() . 'accept.success');
    }
}
