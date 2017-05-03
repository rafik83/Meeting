<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignToGroupHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->sheetRepository = $sheetRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param BatchAssignToGroup $batchAssignToGroup
     *
     * @return BatchResult
     */
    public function handle(BatchAssignToGroup $batchAssignToGroup)
    {
        $locale = $batchAssignToGroup->locale;
        $sheets = $this->sheetRepository->getSheetsById($batchAssignToGroup->ids);

        // Retrieve the sheets without group to warn when a sheet has already a group
        $sheetsWithoutGroup = $this->sheetRepository->getSheetsWithoutGroupInGivenSheets($sheets);
        $sheetsAlreadyWithGroup = [];

        foreach ($sheets as $key => $sheet) {
            if (!isset($sheetsWithoutGroup[$sheet->getId()])) {
                $sheetsAlreadyWithGroup[$sheet->getId()] = $sheet;

                unset($sheets[$key]);

                continue;
            }

            $sheet->setGroup($batchAssignToGroup->group);
            $this->sheetRepository->set($sheet);
        }

        $ignoredSheetsMessage = null;
        $message = 'flash.admin.sheet_batch.assignToGroup.success';

        if (!empty($sheetsAlreadyWithGroup)) {
            $message = 'flash.admin.sheet_batch.assignToGroup.ignoredSheets';

            $ignoredSheetsMessage = implode(', ', array_map(function (Sheet $sheet) use ($locale) {
                return $this->sheetInfoGuesser->guessSheetTitle(
                    $sheet,
                    $locale
                );
            }, $sheetsAlreadyWithGroup));
        }

        return new BatchResult(count($sheets), $message, $ignoredSheetsMessage);
    }
}
