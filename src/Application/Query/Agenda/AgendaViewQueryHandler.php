<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Participant\GetParticipantTypes;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
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

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var ExtraDataRepository */
    private $extraDataRepository;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var GetParticipantTypes */
    private $getParticipantTypes;

    public function __construct(
        DayRepositoryInterface $dayRepository,
        SheetRepositoryInterface $sheetRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        MeetingRepositoryInterface $meetingRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker,
        ExtraDataRepository $extraDataRepository,
        GetTimezoneHelper $getTimezoneHelper,
        GetParticipantTypes $getParticipantTypes
    ) {
        $this->dayRepository = $dayRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->massUnavailabilityRepository = $massUnavailabilityRepository;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->meetingRepository = $meetingRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->extraDataRepository = $extraDataRepository;
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->getParticipantTypes = $getParticipantTypes;
    }

    /**
     * @param AgendaViewQuery $query
     *
     * @return AgendaView
     */
    public function handle(AgendaViewQuery $query): AgendaView
    {
        $eventDays   = $this->dayRepository->findByEvent($query->event);
        $participant = $query->participant;
        $sheet       = $query->sheet;

        $isUserParticipantMultipleSheet = $this->sheetRepository->isUserParticipantMultipleSheetsInEvent(
            $participant->getUser(),
            $query->event
        );

        $isUserAloneParticipant = null !== $query->userViewing ?
            ParticipantHelper::isUserAloneParticipant($query->userViewing, $sheet)
            : false
        ;

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant($query->event, $query->participant);

        $participants = $this->participantViewQueryHandler->handle(
            new ParticipantViewQuery($sheet->getParticipants()->toArray(), $query->locale)
        );

        if (empty($eventDays)) {
            return new AgendaView([], $timezone, $sheet, $participant, $isUserAloneParticipant, $participants, false);
        }

        $unavailabilities        = [];
        $meetings                = [];
        $happeningParticipations = [];
        $masses                  = [];

        if ($query->sheet->attend()) {
            $unavailabilities = $this->unavailabilityRepository->findByUserAndEvent(
                $participant->getUser(),
                $query->event
            );

            $masses = $this->massUnavailabilityRepository->findByTypes(
                $this->getParticipantTypes->handle($participant),
                $query->locale
            );

            $happeningParticipations = $this
                ->happeningParticipationRepository
                ->findByUser($participant->getUser(), $query->event, true);

            if ($this->meetingPublishedAccessChecker->allowedToAccess($query->event)) {
                $meetings = $this->meetingRepository->findByUserAndEvent($participant->getUser(), $query->event);
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
                    $meetings
                )
            );
        }

        $isPhoneConfirmationRequired = false;

        if (true === $this->validationRequiredChecker->handle($sheet, $query->userViewing)) {
            if (null === $this->extraDataRepository->getExtraDataForEventNameAndUser(
                $query->event,
                Type::PHONE_CONFIRMATION_IGNORED,
                $query->userViewing)
            ) {
                $isPhoneConfirmationRequired = true;
            }
        }

        return new AgendaView(
            $dayViews,
            $timezone,
            $sheet,
            $participant,
            $isUserAloneParticipant,
            $participants,
            $isPhoneConfirmationRequired
        );
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
