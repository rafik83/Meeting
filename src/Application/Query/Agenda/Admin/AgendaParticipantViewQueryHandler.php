<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\AgendaParticipantView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class AgendaParticipantViewQueryHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var AgendaDayViewQueryHandler
     */
    private $agendaDayViewQueryHandler;

    /**
     * @param DayRepositoryInterface    $dayRepository
     * @param AgendaDayViewQueryHandler $agendaDayViewQueryHandler
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler
    ) {
        $this->dayRepository             = $dayRepository;
        $this->agendaDayViewQueryHandler = $agendaDayViewQueryHandler;
    }

    /**
     * @param AgendaParticipantViewQuery $query
     *
     * @return AgendaParticipantView
     */
    public function handle(AgendaParticipantViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                new AgendaDayViewQuery(
                    $query->sheet,
                    $day,
                    $query->participant,
                    $query->locale,
                    $query->happeningParticipations,
                    $query->unavailabilites,
                    $query->masses,
                    $query->meetings
                )
            );
        }

        return new AgendaParticipantView($dayViews);
    }
}
