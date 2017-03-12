<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Application\View\Planning\PlanningView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class PlanningViewQueryHandler
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
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $assignmentRepository;

    /**
     * @param DayRepositoryInterface                    $dayRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MassAssignmentRepositoryInterface         $assignmentRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param DayViewQueryHandler                       $dayViewQueryHandler
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MassAssignmentRepositoryInterface $assignmentRepository,
        MeetingRepositoryInterface $meetingRepository,
        DayViewQueryHandler $dayViewQueryHandler
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->assignmentRepository             = $assignmentRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepository                = $meetingRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
    }

    /**
     * @var array
     */
    private $eventDays;

    /** @var array */
    private $meetings = [];

    /** @var null|array */
    private $masses = null;

    /** @var array */
    private $unavailabilities = [];

    /** @var array */
    private $assignments = [];

    /** @var array */
    private $happeningParticipations = [];

    /**
     * @param PlanningViewQuery $query
     *
     * @return PlanningView
     */
    public function handle(PlanningViewQuery $query)
    {
        if ($this->eventDays === null) {
            $this->eventDays = $this->dayRepository->findByEvent($query->event);
        }

        if (empty($this->eventDays)) {
            return new PlanningView([]);
        }

        $participant = $query->participant;
        $sheet       = $query->participant->getSheet();

        $this->loadData($participant);

        $days = [];

        foreach ($this->eventDays as $day) {
            $days[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $sheet,
                    $day,
                    $query->locale,
                    $this->unavailabilities[$participant->getId()],
                    $this->happeningParticipations[$participant->getId()],
                    $this->masses,
                    $this->assignments[$participant->getId()],
                    $this->meetings[$participant->getId()]
                )
            );
        }

        return new PlanningView($days);
    }

    /**
     * @param Participant $participant
     */
    private function loadData(Participant $participant)
    {
        if ($this->masses === null) {
            $this->masses = $this->massUnavailabilityRepository->findNotDispatchedByEvent($participant->getSheet()->getEvent());
        }

        if (!isset($this->assignments[$participant->getId()])) {
            $this->assignments[$participant->getId()] = $this->assignmentRepository->findEnabledByParticipant($participant);
        }

        if (!isset($this->meetings[$participant->getId()])) {
            $this->meetings[$participant->getId()] = $this->meetingRepository->findByParticipant($participant);
        }

        if (!isset($this->unavailabilities[$participant->getId()])) {
            $this->unavailabilities[$participant->getId()] = $this->unavailabilityRepository->findByParticipant($participant);
        }

        if (!isset($this->happeningParticipations[$participant->getId()])) {
            $this->happeningParticipations[$participant->getId()] = $this->happeningParticipationRepository->findByParticipant($participant);
        }
    }

    /**
     * @param Participant[] $participants
     */
    public function preloadForParticipants(array $participants)
    {
        if (!empty($participants)) {
            $firstParticipant = reset($participants);
            $event            = $firstParticipant->getSheet()->getEvent();

            $this->masses = $this->massUnavailabilityRepository->findByEvent($event);
        }

        $this->assignAssignmentByParticipant($this->assignmentRepository->findEnabledByParticipants($participants));
        $this->assignMeetingByParticipant($this->meetingRepository->findByParticipants($participants));
        $this->assignUnavailabilitiesByParticipant($this->unavailabilityRepository->findByParticipants($participants));
        $this->assignHappeningsByParticipant($this->happeningParticipationRepository->findByParticipants($participants));
    }

    /**
     * @param Meeting[] $meetings
     */
    private function assignMeetingByParticipant(array $meetings)
    {
        foreach ($meetings as $meeting) {
            foreach ($meeting->getAllParticipants() as $participant) {
                $this->meetings[$participant->getId()][] = $meeting;
            }
        }
    }

    /**
     * @param MassAssignment[] $assignments
     */
    private function assignAssignmentByParticipant(array $assignments)
    {
        foreach ($assignments as $assignment) {
            $this->assignments[$assignment->getParticipant()->getId()][] = $assignment;
        }
    }

    /**
     * @param Unavailability[] $unavailabilities
     */
    private function assignUnavailabilitiesByParticipant(array $unavailabilities)
    {
        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilities[$unavailability->getParticipant()->getId()][] = $unavailability;
        }
    }

    /**
     * @param HappeningParticipation[] $happenings
     */
    private function assignHappeningsByParticipant(array $happenings)
    {
        foreach ($happenings as $happening) {
            $this->happeningParticipations[$happening->getParticipant()->getId()][] = $happening;
        }
    }

    /**
     * @param Event $event
     */
    public function preloadForEvent(Event $event)
    {
        $this->masses = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);
        $this->assignMeetingByParticipant($this->meetingRepository->getAllByEvent($event));
        $this->assignAssignmentByParticipant($this->assignmentRepository->findEnabledByEvent($event));
        $this->assignUnavailabilitiesByParticipant($this->unavailabilityRepository->getByEvent($event));
        $this->assignHappeningsByParticipant($this->happeningParticipationRepository->getByEvent($event));
    }
}
