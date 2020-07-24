<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TypeView[] */
    private $types = [];

    /** @var IndicatorCalculator */
    private $indicatorCalculator;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        IndicatorCalculator $indicatorCalculator,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->indicatorCalculator = $indicatorCalculator;
        $this->meetingRepository   = $meetingRepository;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView[]
     */
    public function handle(SheetViewQuery $query): array
    {
        $this->orderTypeById($query);
        $sheets = $this->sheetRepository->getSheetsInCatalogByEvent($query->event);

        $meetingCount = [];

        if (!$query->isSolutionFromScratch()) {
            $meetingCount = $this->meetingRepository->countMeetingsOfEvent($query->event);
        }

        $sheetViews = [];

        /** @var Sheet $sheet */
        foreach ($sheets as $sheet) {
            $indicator = $this->indicatorCalculator->getIndicator($sheet);
            $possibleMeetingQuantity = $indicator->possibleMeetingsQuantity;

            if (!empty($meetingCount) && isset($meetingCount[$sheet->getId()]['countMeetings'])) {
                // In case of solution with existing meeting, we overwrite the number of possible meeting quantity
                // with the max between existing meeting and possibileMeetingQuantity
                // Therefore, if the admin have added meeting previously, which overcome the number of possible meeting
                // The planner is not blocked with the extra meetings
                $possibleMeetingQuantity = max(
                    $meetingCount[$sheet->getId()]['countMeetings'],
                    $possibleMeetingQuantity
                );
            }

            $sheetViews[] = new SheetView(
                $sheet->getId(),
                $this->types[$sheet->getType()->getId()],
                $indicator->sheetsPlanningQuantity,
                $possibleMeetingQuantity
            );
        }

        return $sheetViews;
    }

    private function orderTypeById(SheetViewQuery $query): void
    {
        foreach ($query->types as $type) {
            $this->types[$type->id] = $type;
        }
    }
}
