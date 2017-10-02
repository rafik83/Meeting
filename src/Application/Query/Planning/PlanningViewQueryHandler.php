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
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class PlanningViewQueryHandler
{
    /** @var DayRepositoryInterface */
    private $dayRepository;

    /** @var DayViewQueryHandler */
    private $dayViewQueryHandler;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var MassRepositoryInterface */
    private $massUnavailabilityRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var MassAssignmentRepositoryInterface */
    private $assignmentRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var array */
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
     * @param UserRepositoryInterface                   $userRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MassAssignmentRepositoryInterface $assignmentRepository,
        MeetingRepositoryInterface $meetingRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->assignmentRepository             = $assignmentRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepository                = $meetingRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
        $this->userRepository                   = $userRepository;
        $this->sheetRepository                  = $sheetRepository;
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
            return new PlanningView([], $query->event->getTimeZone());
        }

        $this->loadData($query->event, $query->user);

        $days = [];
        $userAvailableLocale = $query->event->getAvailableLocale($query->locale);

        foreach ($this->eventDays as $day) {
            $days[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $query->user,
                    $day,
                    $userAvailableLocale,
                    $this->unavailabilities[$query->user->getId()],
                    $this->happeningParticipations[$query->user->getId()],
                    $this->masses,
                    $this->assignments[$query->user->getId()],
                    $this->meetings[$query->user->getId()]
                )
            );
        }

        $isUserMultipleSheet = $this->sheetRepository->isUserParticipantMultipleSheetsInEvent($query->user, $query->event);

        return new PlanningView($days, $query->event->getTimeZone(), $isUserMultipleSheet);
    }

    /**
     * @param Event $event
     * @param User $user
     */
    private function loadData(Event $event, User $user)
    {
        if ($this->masses === null) {
            $this->masses = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);
        }

        if (!isset($this->assignments[$user->getId()])) {
            $this->assignments[$user->getId()] = $this->assignmentRepository->findEnabledByUserAndEvent($user, $event);
        }

        if (!isset($this->meetings[$user->getId()])) {
            $this->meetings[$user->getId()] = $this->meetingRepository->findByUserAndEvent($user, $event);
        }

        if (!isset($this->unavailabilities[$user->getId()])) {
            $this->unavailabilities[$user->getId()] = $this->unavailabilityRepository->findByUserAndEvent($user, $event);
        }

        if (!isset($this->happeningParticipations[$user->getId()])) {
            $this->happeningParticipations[$user->getId()] = $this->happeningParticipationRepository->findByUser($user, $event);
        }
    }

    /**
     * @param Event  $event
     * @param User[] $users
     */
    public function preloadForEventAndUsers(Event $event, array &$users)
    {
        if (!empty($users)) {
            $this->masses = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);

            $this->assignAssignmentByUser($this->assignmentRepository->findEnabledByEventAndUsers($event, $users));
            $this->assignMeetingByParticipant($this->meetingRepository->findByEventAndUsers($event, $users));
            $this->assignUnavailabilitiesByUser($this->unavailabilityRepository->findByEventAndUsers($event, $users));
            $this->assignHappeningsByUser($this->happeningParticipationRepository->findByEventAndUsers($event, $users));
            $this->addEmptyForUsers($users);
        }
    }

    /**
     * @param Meeting[] $meetings
     */
    private function assignMeetingByParticipant(array $meetings)
    {
        foreach ($meetings as $meeting) {
            foreach ($meeting->getAllParticipants() as $participant) {
                $this->meetings[$participant->getUser()->getId()][] = $meeting;
            }
        }
    }

    /**
     * @param MassAssignment[] $assignments
     */
    private function assignAssignmentByUser(array $assignments)
    {
        foreach ($assignments as $assignment) {
            $this->assignments[$assignment->getUser()->getId()][] = $assignment;
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
        $this->assignAssignmentByUser($this->assignmentRepository->findEnabledByEvent($event));
        $this->assignUnavailabilitiesByUser($this->unavailabilityRepository->getByEvent($event));
        $this->assignHappeningsByUser($this->happeningParticipationRepository->getByEvent($event));
        $this->addEmptyForUsers($this->userRepository->findByEvent($event));
    }

    /**
     * This method is use to set an empty array for user without mass/unavailability/assignment/etc...
     * @param array $users
     */
    private function addEmptyForUsers(array $users)
    {
        foreach ($users as $user) {
            if (!isset($this->assignments[$user->getId()])) {
                $this->assignments[$user->getId()] = [];
            }

            if (!isset($this->meetings[$user->getId()])) {
                $this->meetings[$user->getId()] = [];
            }

            if (!isset($this->unavailabilities[$user->getId()])) {
                $this->unavailabilities[$user->getId()] = [];
            }

            if (!isset($this->happeningParticipations[$user->getId()])) {
                $this->happeningParticipations[$user->getId()] = [];
            }
        }
    }
}
