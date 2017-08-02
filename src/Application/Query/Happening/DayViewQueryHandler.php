<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Happening\DayView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class DayViewQueryHandler
{
    /**
     * @var HappeningViewQueryHandler
     */
    private $happeningViewQueryHandler;

    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var MassUnavailabilityViewQueryHandler
     */
    private $massUnavailabilityViewQueryHandler;

    /**
     * @param HappeningRepositoryInterface       $happeningRepository
     * @param HappeningViewQueryHandler          $happeningViewQueryHandler
     * @param MassUnavailabilityViewQueryHandler $massUnavailabilityViewQueryHandler
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningViewQueryHandler $happeningViewQueryHandler,
        MassUnavailabilityViewQueryHandler $massUnavailabilityViewQueryHandler
    ) {
        $this->happeningRepository                = $happeningRepository;
        $this->happeningViewQueryHandler          = $happeningViewQueryHandler;
        $this->massUnavailabilityViewQueryHandler = $massUnavailabilityViewQueryHandler;
    }

    /**
     * @param DayViewQuery $query
     *
     * @return DayView
     */
    public function handle(DayViewQuery $query)
    {
        $happenings = $this->happeningRepository->findByEventAndDayAndCategory(
            $query->event,
            $query->eventDay->getDay(),
            $query->category
        );

        $happeningViews = [];
        $massViews      = [];

        foreach ($happenings as $happening) {
            $happeningViews[] = $this->happeningViewQueryHandler->handle(
                new HappeningViewQuery($happening, $query->event, $query->locale)
            );
        }

        foreach ($query->masses as $mass) {
            if ($mass->getBegin() >= $query->eventDay->getStartTime()
                && $mass->getEnd() <= $query->eventDay->getEndTime()
            ) {
                $massViews[] = $this->massUnavailabilityViewQueryHandler->handle(
                    new MassUnavailabilityViewQuery($mass, $query->event, $query->locale)
                );
            }
        }

        return new DayView(
            $query->eventDay->getStartTime(),
            $query->eventDay->getEndTime(),
            $query->event->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $this->mergeAndSortMassViewsAndHappeningViews($happeningViews, $massViews)
        );
    }

    /**
     * Merge array of MassUnavaibilityView and array of HappeningView
     * Then sort it by begin date, if begin date are equals the one who have the lowest end date rule
     *
     * @param array $happeningViews
     * @param array $massUnavailabilityView
     *
     * @return array of MassUnavaibilityView and HappeningView
     */
    private function mergeAndSortMassViewsAndHappeningViews(array $happeningViews, array $massUnavailabilityView): array
    {
        $massesAndHappenings = array_merge($happeningViews, $massUnavailabilityView);

        usort($massesAndHappenings, function (AbstractTimeEntityView $first, AbstractTimeEntityView $second) {
            if ($first->begin === $second->begin) {
                return 0;
            }

            return $first->begin < $second->begin ? -1 : 1;
        });

        return $massesAndHappenings;
    }
}
