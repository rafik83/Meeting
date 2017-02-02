<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class MeetingUpdateSpotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param SpotRepositoryInterface $spotRepository
     * @param SheetInfoGuesser        $sheetInfoGuesser
     */
    public function __construct(SpotRepositoryInterface $spotRepository, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->spotRepository   = $spotRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param MeetingUpdateSpotViewQuery $query
     *
     * @return MeetingUpdateSpotView
     */
    public function handle(MeetingUpdateSpotViewQuery $query)
    {
        $meeting = $query->meeting;

        return new MeetingUpdateSpotView(
            $meeting->getId(),
            $meeting->getSpot()->getId(),
            $meeting->isBlockedSlot(),
            $meeting->isBlockedSpot(),
            array_map(function (Spot $spot) use ($meeting) {
                $assignedSheetTitle = $this->getAssignedSheetTitle($spot, $meeting);
                $label = null === $assignedSheetTitle
                    ? $spot->getReference()
                    : sprintf(
                        '%s - %s - %s',
                        $spot->getReference(),
                        $assignedSheetTitle,
                        $spot->isVisio()
                    );

                return new SpotView(
                    $spot->getId(),
                    $label
                );
            }, $this->spotRepository->getSpotsForMeeting($meeting, $query->visio))
        );
    }

    /**
     * @param Spot    $spot
     * @param Meeting $meeting
     *
     * @return null|string
     */
    private function getAssignedSheetTitle(Spot &$spot, Meeting &$meeting)
    {
        foreach ($meeting->getSheets() as $sheet) {
            if (null !== $sheet->getSpot() && $sheet->getSpot()->getId() === $spot->getId()) {
                return $this->sheetInfoGuesser->guessSheetTitle($sheet);
            }
        }

        return null;
    }
}
