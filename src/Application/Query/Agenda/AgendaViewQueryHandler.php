<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AgendaViewQueryHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DayViewQueryHandler
     */
    private $dayViewQueryHandler;

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
     * @var ParticipantViewQueryHandler
     */
    private $participantViewQueryHandler;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @param DayRepositoryInterface                    $dayRepository
     * @param DayViewQueryHandler                       $dayViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param ParticipantViewQueryHandler               $participantViewQueryHandler
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param MeetingPublishedAccessChecker             $meetingPublishedAccessChecker
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        MeetingRepositoryInterface $meetingRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->participantViewQueryHandler      = $participantViewQueryHandler;
        $this->meetingRepository                = $meetingRepository;
        $this->meetingPublishedAccessChecker    = $meetingPublishedAccessChecker;
    }

    /**
     * @param AgendaViewQuery $query
     *
     * @return AgendaView
     */
    public function handle(AgendaViewQuery $query)
    {
        $eventDays              = $this->dayRepository->findByEvent($query->event);
        $participant            = $query->participant;
        $sheet                  = $query->sheet;
        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($query->user, $sheet);

        $participants = $this->participantViewQueryHandler->handle(
            new ParticipantViewQuery($sheet->getParticipants()->toArray(), $query->locale)
        );

        if (empty($eventDays)) {
            return new AgendaView([], $sheet, $participant, $isUserAloneParticipant, $participants);
        }

        $happeningParticipations = $this->happeningParticipationRepository->findByParticipant($participant);
        $unavailabilites         = $this->unavailabilityRepository->findByParticipant($participant);
        $masses                  = $this->massUnavailabilityRepository->findByEvent($query->event, $query->locale);
        $meetings                = [];

        if ($this->meetingPublishedAccessChecker->allowedToAccess($query->event)) {
            $meetings = $this->meetingRepository->findByParticipant($participant);
        }

        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day,
                    $sheet,
                    $query->event,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilites,
                    $masses,
                    $meetings
                )
            );
        }

        return new AgendaView($dayViews, $sheet, $participant, $isUserAloneParticipant, $participants);
    }
}
