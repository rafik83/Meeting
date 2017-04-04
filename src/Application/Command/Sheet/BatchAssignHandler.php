<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * BatchAssignHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
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

        if (!$batchAssign->admin->isOrganizer() && !$batchAssign->admin->isOperator()) {
            throw new SheetException('Follower must be an organizer or operator.');
        }

        $this->sheetRepository->batchAssignBySheetsId($batchAssign->ids, $batchAssign->admin);

        return new BatchResult(count($sheets), $batchAssign->getMessage() . 'assign.success');
    }
}
