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
    const MESSAGE_ASSIGN_SUCCESS            = 'flash.admin.sheet_batch.assignToGroup.success';
    const MESSAGE_ASSIGN_AND_IGNORED_SHEETS = 'flash.admin.sheet_batch.assignToGroup.ignoredSheets';

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
        $this->sheetRepository  = $sheetRepository;
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

        if (null !== $batchAssignToGroup->group) {
            return $this->assign($sheets, $batchAssignToGroup->group, $locale);
        }
    }

    /**
     * @param Sheet[]     $sheets
     * @param Sheet\Group $group
     * @param string      $locale
     *
     * @return BatchResult
     */
    private function assign(array $sheets, Sheet\Group $group, $locale)
    {
        $sheetsAlreadyWithGroup = [];

        foreach ($sheets as $key => $sheet) {
            if ($sheet->hasGroup()) {
                $sheetsAlreadyWithGroup[$sheet->getId()] = $sheet;

                unset($sheets[$key]);

                continue;
            }

            $sheet->setGroup($group);
            $this->sheetRepository->set($sheet);
        }

        $ignoredSheetsMessage = null;
        $message              = self::MESSAGE_ASSIGN_SUCCESS;

        if (!empty($sheetsAlreadyWithGroup)) {
            $message = self::MESSAGE_ASSIGN_AND_IGNORED_SHEETS;

            $ignoredSheetsMessage = implode(
                ', ',
                array_map(
                    function (Sheet $sheet) use ($locale) {
                        return $this->sheetInfoGuesser->guessSheetTitle(
                            $sheet,
                            $locale
                        );
                    },
                    $sheetsAlreadyWithGroup
                )
            );
        }

        return new BatchResult(count($sheets), $message, $ignoredSheetsMessage);
    }
}
