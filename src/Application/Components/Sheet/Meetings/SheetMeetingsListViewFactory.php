<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetMeetingsListViewFactory
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->sheetRepository  = $sheetRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param int    $page
     * @param int    $limit
     *
     * @return PaginatedResult
     */
    public function paginate(Event $event, $locale, $page, $limit)
    {
        $sheets = $this->sheetRepository
            ->getSheetsMeetingsStats($event, $locale, $page, $limit);

        $sheets = array_map(function (array $sheet) use ($locale) {
            return $this->createFromSheet($sheet[0], $locale, $sheet['requestsNumber'], $sheet['meetingsNumber'], $sheet['requestsTransformation']);
        }, $sheets);

        return $sheets;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $requestsNumber
     * @param int    $meetingsNumber
     * @param float  $requestsTranformation
     *
     * @return SheetMeetingsListView
     */
    public function createFromSheet(Sheet $sheet, $locale, $requestsNumber, $meetingsNumber, $requestsTranformation)
    {
        return new SheetMeetingsListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            $sheet->getType()->getTitle($locale),
            $requestsNumber,
            $meetingsNumber,
            $requestsTranformation
        );
    }
}
