<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Viewed;

use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class ViewedSheetListViewQueryHandler
{
    /** @var SheetViewedRepositoryInterface */
    public $sheetViewedRepository;

    /**
     * ViewedSheetListViewQueryHandler constructor.
     *
     * @param SheetViewedRepositoryInterface $sheetViewedRepository
     */
    public function __construct(SheetViewedRepositoryInterface $sheetViewedRepository)
    {
        $this->sheetViewedRepository = $sheetViewedRepository;
    }

    /**
     * @param ViewedSheetListViewQuery $viewedSheetListViewQuery
     *
     * @return array array of seen sheets indexed by seenSheet id
     */
    public function handle(ViewedSheetListViewQuery $viewedSheetListViewQuery)
    {
        $seenSheets = $this->sheetViewedRepository->getSheetsAlreadySeenByUser(
            $viewedSheetListViewQuery->user,
            $viewedSheetListViewQuery->sheets
        );

        if (empty($seenSheets)) {
            return [];
        }

        $seenSheetsIndexed = [];
        foreach ($seenSheets as $seenSheet) {
            $seenSheetsIndexed[$seenSheet->getSheet()->getId()] = true;
        }

        return $seenSheetsIndexed;
    }
}
