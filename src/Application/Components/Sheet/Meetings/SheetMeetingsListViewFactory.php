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
use Proximum\Vimeet\Domain\Model\Event;
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
     *
     * @return array
     */
    public function findAll(Event $event, $locale)
    {
        $sheets = $this->sheetRepository
            ->getSheetsMeetingsStats($event, $locale);

        $sheets = array_map(function (array $sheet) use ($locale) {
            return $this->createFromSheet(
                $sheet[0],
                $locale,
                $sheet['meetingsRequestsNumber'],
                $sheet['meetingsPropositionsNumber'],
                $sheet['requestsNumber'],
                $sheet['propositionsNumber'],
                $sheet['requestsTransformation'],
                $sheet['propositionsTransformation'],
                $sheet['transformationTotal']
            );
        }, $sheets);

        return $sheets;
    }

    /**
     * @param Sheet $sheet
     * @param int   $locale
     * @param int   $meetingsRequestsNumber
     * @param int   $meetingsPropositionsNumber
     * @param int   $requestsNumber
     * @param int   $propositionsNumber
     * @param float $requestsTransformation
     * @param float $propositionsTransformation
     * @param float $transformationTotal
     *
     * @return SheetMeetingsListView
     */
    public function createFromSheet(
        Sheet $sheet,
        $locale,
        $meetingsRequestsNumber,
        $meetingsPropositionsNumber,
        $requestsNumber,
        $propositionsNumber,
        $requestsTransformation,
        $propositionsTransformation,
        $transformationTotal
    ) {
        return new SheetMeetingsListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            $sheet->getType()->getTitle($locale),
            $meetingsRequestsNumber,
            $meetingsPropositionsNumber,
            $requestsNumber,
            $propositionsNumber,
            $requestsTransformation,
            $propositionsTransformation,
            $transformationTotal
        );
    }
}
