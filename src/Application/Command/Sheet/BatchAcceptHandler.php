<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

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
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param AcceptHandler            $acceptHandler
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AcceptHandler $acceptHandler,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->acceptHandler   = $acceptHandler;
        $this->datetime        = $datetime;
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

        return new BatchResult(count($sheets), $batchAccept->getMessage() . 'accept.success');
    }
}
