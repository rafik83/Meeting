<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class PostBatchDuplicateSheetsHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->datetime = $datetime;
    }

    public function handle(PostBatchDuplicateSheets $command): void
    {
        foreach ($command->sheets as $sheet) {
            $this->sheetRepository->add(Sheet::duplicateSheetFrom($sheet, $command->type, $this->datetime));
        }
    }
}
