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
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
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

    /**
     * @param DayRepositoryInterface                    $dayRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     * @param DayViewQueryHandler                       $dayViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param ParticipantViewQueryHandler               $participantViewQueryHandler
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param MeetingPublishedAccessChecker             $meetingPublishedAccessChecker
     * @param ValidationRequiredChecker                 $validationRequiredChecker
     * @param ExtraDataRepository                       $extraDataRepository
     */
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
        ExtraDataRepository $extraDataRepository
    ) {
        $this->dayRepository                    = $dayRepository;
        $this->sheetRepository                  = $sheetRepository;
        $this->dayViewQueryHandler              = $dayViewQueryHandler;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->participantViewQueryHandler      = $participantViewQueryHandler;
        $this->meetingRepository                = $meetingRepository;
        $this->meetingPublishedAccessChecker    = $meetingPublishedAccessChecker;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->extraDataRepository = $extraDataRepository;
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

        $participants = $this->participantViewQueryHandler->handle(
            new ParticipantViewQuery($sheet->getParticipants()->toArray(), $query->locale)
        );

        if (empty($eventDays)) {
            return new AgendaView([], $sheet, $participant, $isUserAloneParticipant, $participants, false);
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
            $masses = $this->massUnavailabilityRepository->findByEvent($query->event, $query->locale);

            $happeningParticipations = $this
                ->happeningParticipationRepository
                ->findByUser($participant->getUser(), $query->event, ['disabled' => false]);

            if ($this->meetingPublishedAccessChecker->allowedToAccess($query->event)) {
                $meetings = $this->meetingRepository->findByUserAndEvent($participant->getUser(), $query->event);
            }
        }

        $dayViews = [];

        foreach ($eventDays as $day) {
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
            $sheet,
            $participant,
            $isUserAloneParticipant,
            $participants,
            $isPhoneConfirmationRequired
        );
    }
}
