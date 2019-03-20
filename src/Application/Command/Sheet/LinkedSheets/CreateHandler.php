<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
{
    /** @var LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $linkedSheets = new LinkedSheets(
            $command->event,
            $this->dateTime
        );

        foreach ($command->sheetViews as $sheetView) {
            $sheet = $this->sheetRepository->getSheetById($sheetView->id);

            $sheet->setLinkedSheets($linkedSheets);
        }

        $this->linkedSheetsRepository->add($linkedSheets);
    }
}
