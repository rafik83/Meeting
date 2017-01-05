<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var IndicatorCalculator
     */
    private $indicatorCalculator;

    /**
     * SheetListViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param MeetingRepositoryInterface $meetingRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param IndicatorCalculator        $indicatorCalculator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        IndicatorCalculator $indicatorCalculator
    ) {
        $this->meetingRepository        = $meetingRepository;
        $this->sheetRepository          = $sheetRepository;
        $this->requestRepository        = $requestRepository;
        $this->sheetInfoGuesser         = $sheetInfoGuesser;
        $this->indicatorCalculator      = $indicatorCalculator;
    }

    /**
     * @param SheetListViewQuery $sheetListViewQuery
     *
     * @return SheetView[]
     */
    public function handle(SheetListViewQuery $sheetListViewQuery)
    {
        $sheetList = [];
        $sheets    = $this->sheetRepository->getSheetsInCatalogByEvent($sheetListViewQuery->event);

        foreach ($sheets as $sheet) {
            // Count the request per sheet
            $request = $this->requestRepository->countRequestSentBySheet($sheet);

            // Count the proposition per sheet
            $propositions = $this->requestRepository->countPropositionReceivedBySheet($sheet);

            $indicator = $this->indicatorCalculator->getIndicator($sheet);

            $sheetList[] = new SheetView(
                $sheet->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($sheet, $sheetListViewQuery->locale),
                $sheet->getType()->getTitle($sheetListViewQuery->locale),
                count($sheet->getParticipants()),
                $request,
                $propositions,
                $this->requestRepository->countApprovedPropositionReceivedBySheet($sheet),
                $indicator->slotTotal,
                $indicator->possibleMeetingsQuantity,
                $this->getPlacedMeetingsNumber($sheet),
                $sheet->getFollower()
            );
        }

        return $sheetList;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    private function getPlacedMeetingsNumber(Sheet $sheet)
    {
        $participants        = $sheet->getParticipants();
        $countPlacedMeetings = 0;

        foreach ($participants as $participant) {
            $countPlacedMeetings += $this->meetingRepository->countByParticipant($participant);
        }

        return $countPlacedMeetings;
    }
}
