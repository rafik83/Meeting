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
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param ValidateHandler          $validateHandler
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, ValidateHandler $validateHandler)
    {
        $this->sheetRepository = $sheetRepository;
        $this->validateHandler = $validateHandler;
    }

    /**
     * @param BatchValidate $batchValidate
     *
     * @return BatchValidateResult
     */
    public function handle(BatchValidate $batchValidate)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchValidate->ids);

        // Ensure all sheets are not validated
        $sheets = array_filter($sheets, function (Sheet $sheet) {
            return !$sheet->isValidated();
        });

        // Validate sheets
        foreach ($sheets as $sheet) {
            $this->validateHandler->handle(new Validate($sheet));
        }

        return new BatchValidateResult(count($sheets));
    }
}
