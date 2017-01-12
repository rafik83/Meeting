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
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

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
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param DayRepositoryInterface    $dayRepository
     * @param AgendaDayViewQueryHandler $agendaDayViewQueryHandler
     * @param ParticipantInfoGuesser    $participantInfoGuesser
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->dayRepository             = $dayRepository;
        $this->agendaDayViewQueryHandler = $agendaDayViewQueryHandler;
        $this->participantInfoGuesser    = $participantInfoGuesser;
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

        foreach ($eventDays as $dayNumber => $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                new AgendaDayViewQuery(
                    $query->sheet,
                    $day,
                    $dayNumber,
                    $query->participant,
                    $query->locale,
                    $query->happeningParticipations,
                    $query->unavailabilites,
                    $query->masses,
                    $query->meetings
                )
            );
        }

        $fullname = $this->participantInfoGuesser->guessParticipantCompleteName($query->participant, $query->locale);

        return new AgendaParticipantView(
            $query->participant->getId(),
            $fullname,
            $dayViews
        );
    }
}
