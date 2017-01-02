<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaParticipantView;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AgendaParticipantViewQueryHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DayViewQueryHandler
     */
    private $agendaDayViewQueryHandler;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var MassRepositoryInterface
     */
    private $massUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepositoryInterface;

    /**
     * @param DayRepositoryInterface                    $dayRepository
     * @param AgendaDayViewQueryHandler                 $agendaDayViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MeetingRepositoryInterface                $meetingRepositoryInterface
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepositoryInterface
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->agendaDayViewQueryHandler        = $agendaDayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepositoryInterface       = $meetingRepositoryInterface;
    }

    public function handle(AgendaParticipantViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        $happeningParticipations = $this->happeningParticipationRepository->findByParticipant($query->participant);
        $unavailabilites         = $this->unavailabilityRepository->findByParticipant($query->participant);
        $masses                  = $this->massUnavailabilityRepository->findByEvent($query->event, $query->locale);
        $meetings                = $this->meetingRepositoryInterface->findByParticipant($query->participant);

        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                $query = new AgendaDayViewQuery(
                    $query->sheet,
                    $day,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilites,
                    $masses,
                    $meetings
                )
            );
        }

        return new AgendaParticipantView($dayViews);
    }
}
