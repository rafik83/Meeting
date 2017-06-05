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
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
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
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var array
     */
    private $eventDays;

    /** @var array */
    private $meetings = [];

    /** @var null|array */
    private $masses = null;

    /** @var array of Unavailability[] indexed by User id */
    private $unavailabilities = [];

    /** @var array */
    private $assignments = [];

    /** @var array */
    private $happeningParticipations = [];

    /**
     * @param DayRepositoryInterface                    $dayRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MassAssignmentRepositoryInterface         $assignmentRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param DayViewQueryHandler                       $dayViewQueryHandler
     * @param ParticipantRepositoryInterface            $participantRepository
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MassAssignmentRepositoryInterface $assignmentRepository,
        MeetingRepositoryInterface $meetingRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->assignmentRepository             = $assignmentRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepository                = $meetingRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
        $this->participantRepository            = $participantRepository;
    }

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
                    $this->unavailabilities[$participant->getUser()->getId()],
                    $this->happeningParticipations[$participant->getUser()->getId()],
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

        if (!isset($this->unavailabilities[$participant->getUser()->getId()])) {
            $this->unavailabilities[$participant->getUser()->getId()] = $this->unavailabilityRepository->findByParticipant($participant);
        }

        if (!isset($this->happeningParticipations[$participant->getUser()->getId()])) {
            $this->happeningParticipations[$participant->getUser()->getId()] = $this->happeningParticipationRepository->findByUser($participant->getUser(), $participant->getSheet()->getEvent());
        }
    }

    /**
     * @param Participant[] $participants
     */
    public function preloadForParticipants(array $participants)
    {
        if (!empty($participants)) {
            $firstParticipant = reset($participants);

            if (false !== $firstParticipant) {
                $event = $firstParticipant->getSheet()->getEvent();
                $this->masses = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);

                $this->assignAssignmentByParticipant($this->assignmentRepository->findEnabledByParticipants($participants));
                $this->assignMeetingByParticipant($this->meetingRepository->findByParticipants($participants));
                $this->assignUnavailabilitiesByUser($this->unavailabilityRepository->findByParticipants($participants));
                $this->assignHappeningsByUser($this->happeningParticipationRepository->findByUsers($participants, $event));
                $this->addEmptyForParticipant($participants);
            }
        }
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
    private function assignUnavailabilitiesByUser(array $unavailabilities)
    {
        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilities[$unavailability->getUser()->getId()][] = $unavailability;
        }
    }

    /**
     * @param HappeningParticipation[] $happenings
     */
    private function assignHappeningsByUser(array $happenings)
    {
        foreach ($happenings as $happening) {
            $this->happeningParticipations[$happening->getUser()->getId()][] = $happening;
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
        $this->assignUnavailabilitiesByUser($this->unavailabilityRepository->getByEvent($event));
        $this->assignHappeningsByUser($this->happeningParticipationRepository->getByEvent($event));
        $this->addEmptyForParticipant($this->participantRepository->findByEvent($event));
    }

    /**
     * This method is use to set an empty array for participant without mass/unavailability/assignment/etc...
     *
     * @param Participant[] $participants
     */
    private function addEmptyForParticipant(array $participants)
    {
        foreach ($participants as $participant) {
            if (!isset($this->assignments[$participant->getId()])) {
                $this->assignments[$participant->getId()] = [];
            }

            if (!isset($this->meetings[$participant->getId()])) {
                $this->meetings[$participant->getId()] = [];
            }

            if (!isset($this->unavailabilities[$participant->getUser()->getId()])) {
                $this->unavailabilities[$participant->getUser()->getId()] = [];
            }

            if (!isset($this->happeningParticipations[$participant->getUser()->getId()])) {
                $this->happeningParticipations[$participant->getUser()->getId()] = [];
            }
        }
    }
}
