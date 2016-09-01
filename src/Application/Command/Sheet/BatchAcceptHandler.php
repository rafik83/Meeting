<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
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
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param AcceptHandler            $acceptHandler
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, AcceptHandler $acceptHandler)
    {
        $this->sheetRepository = $sheetRepository;
        $this->acceptHandler   = $acceptHandler;
    }

    /**
     * @param BatchAccept $batchAccept
     *
     * @return BatchResult
     */
    public function handle(BatchAccept $batchAccept)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchAccept->ids);

        // Ensure all sheets are not accepted
        $sheets = array_filter($sheets, function (Sheet $sheet) {
            return !$sheet->isAccepted();
        });

        // Accept sheets
        foreach ($sheets as $sheet) {
            $this->acceptHandler->handle(new Accept($sheet, $batchAccept->admin, $batchAccept->date));
        }

        return new BatchResult(count($sheets), $batchAccept->getMessage() . 'accept.success');
    }
}
