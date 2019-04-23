<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Helper;

use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class LinkedSheetsTitle
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    public function getSheetTitleView(Sheet $userSheet, Sheet $sheetMet): array
    {
        if (!$sheetMet->hasLinkedSheets()) {
            return [new SheetMetView($sheetMet->getTitle(), false)];
        }

        $linkedSheetTitles = [];
        $linkedSheets = $sheetMet->getLinkedSheets();
        foreach ($linkedSheets->getSheets() as $otherSheet) {
            if ($this->requestRepository->hasApprovedMeetingRequest($userSheet, $otherSheet)) {
                $linkedSheetTitles[] = new SheetMetView($otherSheet->getTitle(), true);
            } else {
                $linkedSheetTitles[] = new SheetMetView($otherSheet->getTitle(), false);
            }
        }

        return $linkedSheetTitles;
    }
}
