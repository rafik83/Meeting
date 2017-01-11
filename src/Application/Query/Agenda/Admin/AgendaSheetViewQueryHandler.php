<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\AgendaSheetView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AgendaSheetViewQueryHandler
{
    /**
     * @var AgendaParticipantViewQueryHandler
     */
    private $agendaParticipantViewQueryHandler;

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
     * @param AgendaParticipantViewQueryHandler         $agendaParticipantViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MeetingRepositoryInterface                $meetingRepositoryInterface
     */
    public function __construct(
        AgendaParticipantViewQueryHandler $agendaParticipantViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepositoryInterface
    ) {
        $this->agendaParticipantViewQueryHandler = $agendaParticipantViewQueryHandler;
        $this->happeningParticipationRepository  = $happeningParticipationRepository;
        $this->unavailabilityRepository          = $unavailabilityRepository;
        $this->massUnavailabilityRepository      = $massUnavailabilityRepository;
        $this->meetingRepositoryInterface        = $meetingRepositoryInterface;
    }

    /**
     * @param AgendaSheetViewQuery $query
     *
     * @return AgendaSheetView
     */
    public function handle(AgendaSheetViewQuery $query)
    {
        $happeningParticipations = $this->happeningParticipationRepository->getByEvent($query->sheet->getEvent());

        $unavailabilites = $this->unavailabilityRepository->getByEvent($query->sheet->getEvent());
        $masses          = $this->massUnavailabilityRepository->findByEvent($query->sheet->getEvent(), $query->locale);
        $meetings        = $this->meetingRepositoryInterface->getAllByEvent($query->sheet->getEvent());

        $participants = [];

        foreach ($query->sheet->getParticipants() as $participant) {
            $participants[] = $this->agendaParticipantViewQueryHandler->handle(
                new AgendaParticipantViewQuery(
                    $participant,
                    $query->sheet->getEvent(),
                    $query->sheet,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilites,
                    $masses,
                    $meetings
                )
            );
        }

        return new AgendaSheetView($participants);
    }
}
