<?php

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
use Proximum\Vimeet\Domain\Unavailability\Mass\FilterMassUnavailabilitiesBySheetsType;

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
    private $eventDays = [];

    /** @var array */
    private $meetings = [];

    /** @var array */
    private $masses = [];

    /** @var array of Unavailability[] indexed by Event id and User id */
    private $unavailabilities = [];

    /** @var array */
    private $assignments = [];

    /** @var array */
    private $happeningParticipations = [];

    /** @var FilterMassUnavailabilitiesBySheetsType */
    private $filterMassUnavailabilitiesBySheetsType;

    public function __construct(
        DayRepositoryInterface $dayRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MassAssignmentRepositoryInterface $assignmentRepository,
        MeetingRepositoryInterface $meetingRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        FilterMassUnavailabilitiesBySheetsType $filterMassUnavailabilitiesBySheetsType
    ) {
        $this->dayRepository = $dayRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->massUnavailabilityRepository = $massUnavailabilityRepository;
        $this->meetingRepository = $meetingRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->filterMassUnavailabilitiesBySheetsType = $filterMassUnavailabilitiesBySheetsType;
    }

    public function handle(PlanningViewQuery $query): PlanningView
    {
        $eventId = $query->event->getId();

        if (!isset($this->eventDays[$eventId])) {
            $this->eventDays[$eventId] = $this->dayRepository->findByEvent($query->event);
        }

        if (empty($this->eventDays[$eventId])) {
            return new PlanningView([], $query->event->getTimeZone());
        }

        $this->loadData($query->event, $query->user);

        $days = [];
        $userAvailableLocale = $query->event->getAvailableLocale($query->locale);

        $sheets = $this->sheetRepository->getSheetsByUserAndEventWhereUserIsParticipant($query->user, $query->event);
        $isUserMultipleSheet = \count($sheets) > 1;
        $filteredMassUnavailabilitiesBySheetsType = $this->filterMassUnavailabilitiesBySheetsType->handle(
            $this->masses[$eventId],
            $sheets
        );

        foreach ($this->eventDays[$eventId] as $day) {
            $days[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $query->event,
                    $query->user,
                    $day,
                    $userAvailableLocale,
                    $this->unavailabilities[$eventId][$query->user->getId()],
                    $this->happeningParticipations[$eventId][$query->user->getId()],
                    $filteredMassUnavailabilitiesBySheetsType,
                    $this->assignments[$eventId][$query->user->getId()],
                    $this->meetings[$eventId][$query->user->getId()]
                )
            );
        }

        return new PlanningView($days, $query->event->getTimeZone(), $isUserMultipleSheet);
    }

    /**
     * @param Event $event
     * @param User  $user
     */
    private function loadData(Event $event, User $user): void
    {
        $eventId = $event->getId();

        if (!isset($this->masses[$eventId])) {
            $this->masses[$eventId] = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);
        }

        if (!isset($this->assignments[$eventId][$user->getId()])) {
            $this->assignments[$eventId][$user->getId()] = $this->assignmentRepository
                ->findEnabledByUserAndEvent($user, $event)
            ;
        }

        if (!isset($this->meetings[$eventId][$user->getId()])) {
            $this->meetings[$eventId][$user->getId()] = $this->meetingRepository
                ->findByUserAndEvent($user, $event)
            ;
        }

        if (!isset($this->unavailabilities[$eventId][$user->getId()])) {
            $this->unavailabilities[$eventId][$user->getId()] = $this->unavailabilityRepository
                ->findByUserAndEvent($user, $event)
            ;
        }

        if (!isset($this->happeningParticipations[$eventId][$user->getId()])) {
            $this->happeningParticipations[$eventId][$user->getId()] = $this->happeningParticipationRepository
                ->findByUser($user, $event, true)
            ;
        }
    }

    /**
     * @param Event  $event
     * @param User[] $users
     */
    public function preloadForEventAndUsers(Event $event, array &$users): void
    {
        if (!empty($users)) {
            $this->masses[$event->getId()] = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);

            $this->assignAssignmentByUser($event, $this->assignmentRepository->findEnabledByEventAndUsers($event, $users));
            $this->assignMeetingByParticipant($event, $this->meetingRepository->findByEventAndUsers($event, $users));
            $this->assignUnavailabilitiesByUser($event, $this->unavailabilityRepository->findByEventAndUsers($event, $users));
            $this->assignHappeningsByUser($event, $this->happeningParticipationRepository->findByEventAndUsers($event, $users));
            $this->addEmptyForUsers($event, $users);
        }
    }

    /**
     * @param Event     $event
     * @param Meeting[] $meetings
     */
    private function assignMeetingByParticipant(Event $event, array $meetings)
    {
        foreach ($meetings as $meeting) {
            foreach ($meeting->getAllParticipants() as $participant) {
                $this->meetings[$event->getId()][$participant->getUser()->getId()][] = $meeting;
            }
        }
    }

    /**
     * @param Event            $event
     * @param MassAssignment[] $assignments
     */
    private function assignAssignmentByUser(Event $event, array $assignments): void
    {
        foreach ($assignments as $assignment) {
            $this->assignments[$event->getId()][$assignment->getUser()->getId()][] = $assignment;
        }
    }

    /**
     * @param Event            $event
     * @param Unavailability[] $unavailabilities
     */
    private function assignUnavailabilitiesByUser(Event $event, array $unavailabilities): void
    {
        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilities[$event->getId()][$unavailability->getUser()->getId()][] = $unavailability;
        }
    }

    /**
     * @param Event                    $event
     * @param HappeningParticipation[] $happenings
     */
    private function assignHappeningsByUser(Event $event, array $happenings): void
    {
        foreach ($happenings as $happening) {
            $this->happeningParticipations[$event->getId()][$happening->getUser()->getId()][] = $happening;
        }
    }

    /**
     * @param Event $event
     */
    public function preloadForEvent(Event $event): void
    {
        $this->masses[$event->getId()] = $this->massUnavailabilityRepository->findNotDispatchedByEvent($event);
        $this->assignMeetingByParticipant($event, $this->meetingRepository->getAllByEvent($event));
        $this->assignAssignmentByUser($event, $this->assignmentRepository->findEnabledByEvent($event));
        $this->assignUnavailabilitiesByUser($event, $this->unavailabilityRepository->getByEvent($event));
        $this->assignHappeningsByUser($event, $this->happeningParticipationRepository->getByEvent($event));
        $this->addEmptyForUsers($event, $this->userRepository->findWithEnabledSheetByEvent($event));
    }

    /**
     * @param Event $event
     */
    public function resetForEvent(Event $event): void
    {
        $this->masses[$event->getId()]                  = [];
        $this->assignments[$event->getId()]             = [];
        $this->meetings[$event->getId()]                = [];
        $this->unavailabilities[$event->getId()]        = [];
        $this->happeningParticipations[$event->getId()] = [];
    }

    /**
     * This method is use to set an empty array for user without mass/unavailability/assignment/etc...
     *
     * @param Event $event
     * @param array $users
     */
    private function addEmptyForUsers(Event $event, array $users): void
    {
        $eventId = $event->getId();

        foreach ($users as $user) {
            if (!isset($this->assignments[$eventId][$user->getId()])) {
                $this->assignments[$eventId][$user->getId()] = [];
            }

            if (!isset($this->meetings[$eventId][$user->getId()])) {
                $this->meetings[$eventId][$user->getId()] = [];
            }

            if (!isset($this->unavailabilities[$eventId][$user->getId()])) {
                $this->unavailabilities[$eventId][$user->getId()] = [];
            }

            if (!isset($this->happeningParticipations[$eventId][$user->getId()])) {
                $this->happeningParticipations[$eventId][$user->getId()] = [];
            }
        }
    }
}
