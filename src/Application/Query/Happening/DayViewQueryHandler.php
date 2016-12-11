<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

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
     * @param HappeningRepositoryInterface $happeningRepository
     * @param HappeningViewQueryHandler    $happeningViewQueryHandler
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningViewQueryHandler $happeningViewQueryHandler
    ) {
        $this->happeningRepository       = $happeningRepository;
        $this->happeningViewQueryHandler = $happeningViewQueryHandler;
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

        foreach ($happenings as $key => $happening) {
            $happeningViews[] = $this->happeningViewQueryHandler->handle(
                new HappeningViewQuery($happening, $query->locale, $key + 1)
            );
        }

        return new DayView(
            $query->eventDay->getStartTime(),
            $query->eventDay->getEndTime(),
            $query->event->getConfiguration()->getScheduleScale(),
            $happeningViews
        );
    }
}
