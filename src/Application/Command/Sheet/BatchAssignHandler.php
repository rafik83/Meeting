<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, JobQueueInterface $jobQueue)
    {
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param BatchAssign $batchAssign
     *
     * @return BatchResult
     * @throws SheetException
     */
    public function handle(BatchAssign $batchAssign)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchAssign->ids);

        if ($batchAssign->admin !== null
            && !$batchAssign->admin->isOrganizer() && !$batchAssign->admin->isOperator()
        ) {
            throw new SheetException('Follower must be an organizer or operator.');
        }

        if (false === $batchAssign->unassigned()) {
            $this->sheetRepository->batchAssignBySheetsId($batchAssign->ids, $batchAssign->admin);
        } else {
            $this->sheetRepository->batchUnAssignBySheetsId($batchAssign->ids);
        }

        $endMessage = 'assign.success';

        if ($batchAssign->unassigned()) {
            $endMessage = 'unassign.success';
        }

        // reindex sheets
        $this->jobQueue->indexSheets($batchAssign->ids);

        return new BatchResult($sheets, $batchAssign->getMessage() . $endMessage);
    }
}
