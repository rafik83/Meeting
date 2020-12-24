<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\GetParticipantTypes;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Domain\User\Agenda\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Repository\User\Event\ExtraDataRepository;

class AgendaViewQueryHandler
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

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var ExtraDataRepository */
    private $extraDataRepository;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var GetParticipantTypes */
    private $getParticipantTypes;

    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var CanRemoveMeeting */
    private $canRemoveMeeting;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var DDayGuesser */
    private $dDayGuesser;

    public function __construct(
        DayRepositoryInterface $dayRepository,
        SheetRepositoryInterface $sheetRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        MeetingRepositoryInterface $meetingRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker,
        ExtraDataRepository $extraDataRepository,
        GetTimezoneHelper $getTimezoneHelper,
        GetParticipantTypes $getParticipantTypes,
        CanMoveMeeting $canMoveMeeting,
        CanRemoveMeeting $canRemoveMeeting,
        IsParticipantVisio $isParticipantVisio,
        DDayGuesser $dDayGuesser
    ) {
        $this->dayRepository = $dayRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->massUnavailabilityRepository = $massUnavailabilityRepository;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->meetingRepository = $meetingRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->extraDataRepository = $extraDataRepository;
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->getParticipantTypes = $getParticipantTypes;
        $this->canMoveMeeting = $canMoveMeeting;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->isParticipantVisio = $isParticipantVisio;
        $this->dDayGuesser = $dDayGuesser;
    }

    public function handle(AgendaViewQuery $query): AgendaView
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);
        $participant = $query->participant;
        $sheet = $query->sheet;
        $canMoveMeeting = $this->canMoveMeeting->isSatisfiedBy($sheet);
        $canRemoveMeeting = $this->canRemoveMeeting->isSatisfiedBy($sheet);
        $user = $participant->getUser();

        $isUserParticipantMultipleSheet = $this->sheetRepository->isUserParticipantMultipleSheetsInEvent(
            $user,
            $query->event
        );

        $isUserAloneParticipant = null !== $query->userViewing ?
            ParticipantHelper::isUserAloneParticipant($query->userViewing, $sheet)
            : false
        ;

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant($query->event, $query->participant);
        $timezoneTranslated = $this->getTimezoneHelper->getTimezoneTranslated($timezone);

        $participants = $this->participantViewQueryHandler->handle(
            new ParticipantViewQuery($sheet->getParticipants()->toArray(), $query->locale)
        );

        if (empty($eventDays)) {
            return new AgendaView(
                [],
                $timezone,
                $timezoneTranslated,
                $sheet,
                $participant,
                $isUserAloneParticipant,
                $isUserParticipantMultipleSheet,
                $participants,
                false,
                $canMoveMeeting,
                $canRemoveMeeting,
                false,
                false
            );
        }

        $unavailabilities = [];
        $meetings = [];
        $happeningParticipations = [];
        $masses = [];
        $meetingSlots = [];

        if ($query->sheet->attend()) {
            $masses = $this->massUnavailabilityRepository->findByTypes(
                $this->getParticipantTypes->handle($participant),
                $query->locale
            );

            if (!$query->allSheet) {
                $unavailabilities = $this->unavailabilityRepository->findByUserAndEvent(
                    $user,
                    $query->event
                );

                $happeningParticipations = $this->getHappeningParticipations($query->event, $user);
            }

            if ($query->allSheet) {
                $meetingSlots = $this->meetingSlotRepository->findByEvent($query->event);
            }

            if ($this->meetingPublishedAccessChecker->allowedToAccess($query->event)) {
                $meetings = $this->getMeetings($query);
            }
        }

        $timezonedDays = $this->getTimezonedDays($eventDays, $timezone);
        $dayViews = [];

        foreach ($timezonedDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $day,
                    $sheet,
                    $query->event,
                    $participant,
                    $query->userViewing,
                    $isUserParticipantMultipleSheet,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilities,
                    $masses,
                    $meetings,
                    $meetingSlots
                )
            );
        }

        $isPhoneConfirmationRequired = $this->validationRequiredChecker->handle($sheet, $query->userViewing)
            && null === $this->extraDataRepository->getExtraDataForEventNameAndUser(
                $query->event,
                Type::PHONE_CONFIRMATION_IGNORED,
                $query->userViewing
            );

        return new AgendaView(
            $dayViews,
            $timezone,
            $timezoneTranslated,
            $sheet,
            $participant,
            $isUserAloneParticipant,
            $isUserParticipantMultipleSheet,
            $participants,
            $isPhoneConfirmationRequired,
            $canMoveMeeting,
            $canRemoveMeeting,
            $this->isParticipantVisio->isSatisfiedBy($participant),
            $this->dDayGuesser->isItDDay($query->event)
        );
    }

    /**
     * @param AgendaViewQuery $query
     *
     * @return Meeting[]
     */
    private function getMeetings(AgendaViewQuery $query): array
    {
        if (!$query->allSheet) {
            return $this->meetingRepository->findByUserAndEvent($query->participant->getUser(), $query->event);
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($query->userViewing, $query->event);

        return $this->meetingRepository->getBySheets($query->event, $sheets);
    }

    private function getHappeningParticipations(Event $event, User $user): array
    {
        $happeningParticipations = $this
            ->happeningParticipationRepository
            ->findByUser($user, $event, true);

        foreach ($happeningParticipations as $happeningParticipation) {
            $happeningsFound[$happeningParticipation->getHappening()->getId()] = true;
        }

        foreach ($this->happeningParticipationRepository->findBySpeaker($user, $event) as $happeningParticipation) {
            if (!isset($happeningsFound[$happeningParticipation->getHappening()->getId()])) {
                $happeningParticipations[] = $happeningParticipation;
            }
        }

        return $happeningParticipations;
    }

    /**
     * @param array  $eventDays
     * @param string $timezone
     *
     * @return TimeRangeView[]
     */
    private function getTimezonedDays(array $eventDays, string $timezone): array
    {
        $timezonedTimeRangeViews = [];

        foreach ($eventDays as $day) {
            $timezonedTimeRangeViews[] = new TimeRangeView(
                DaysHelper::cloneDateTime($day->getBegin(), $timezone),
                DaysHelper::cloneDateTime($day->getEnd(), $timezone)
            );
        }

        return DaysHelper::splitDays($timezonedTimeRangeViews);
    }
}
