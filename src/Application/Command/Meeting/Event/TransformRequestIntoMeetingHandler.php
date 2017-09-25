<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableMeetingView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsBySheetView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantWithPhoneValidated;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransformRequestIntoMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    public $meetingRepository;

    /** @var EventDispatcherInterface */
    public $eventDispatcher;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ParticipantWithPhoneValidated */
    private $participantWithPhoneValidated;

    /** @var AvailableSpots */
    private $availableSpots;

    /**
     * Array of available slots by sheet and by participant
     *
     * @var AvailableSlotsBySheetView
     */
    private $fromSheet;

    /**
     * Array of available slots by sheet and by participant
     *
     * @var AvailableSlotsBySheetView
     */
    private $toSheet;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * TransformRequestIntoMeetingHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param ParticipantWithPhoneValidated  $participantWithPhoneValidated
     * @param AvailableSpots                 $availableSpots
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param EventDispatcherInterface       $eventDispatcher
     * @param \DateTimeInterface             $dateTime
     *
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        ParticipantWithPhoneValidated $participantWithPhoneValidated,
        AvailableSpots $availableSpots,
        MeetingRepositoryInterface $meetingRepository,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingSlotRepository         = $meetingSlotRepository;
        $this->participantWithPhoneValidated = $participantWithPhoneValidated;
        $this->availableSpots                = $availableSpots;
        $this->dateTime                      = $dateTime;
        $this->meetingRepository             = $meetingRepository;
        $this->eventDispatcher               = $eventDispatcher;
    }

    /**
     * @param TransformRequestIntoMeeting $query
     *
     * @throw NoSpotsAvailableForThisSlotAndMeetingException
     * @return Meeting
     */
    public function handle(TransformRequestIntoMeeting $query): Meeting
    {
        $fromSheet           = $query->request->getFromSheet();
        $toSheet             = $query->request->getToSheet();
        $fromParticipants    = $query->request->getFromParticipantsArray();
        $toParticipants      = $query->request->getToParticipantsArray();
        $fromHasNoPreference = $query->request->hasNoPreference($fromSheet);
        $toHasNoPreference   = $query->request->hasNoPreference($toSheet);

        $this->fromSheet = new AvailableSlotsBySheetView($fromSheet, $fromParticipants, $fromHasNoPreference);
        $this->toSheet   = new AvailableSlotsBySheetView($toSheet, $toParticipants, $toHasNoPreference);

        if ($fromHasNoPreference) {
            $fromParticipants = $query->request->getFromSheet()->getParticipantsArray();

            $this->fromSheet->availableSlotsByParticipant = $this->getAvailableSlotsByParticipants(
                $query->event,
                $fromParticipants
            );
        } else {
            $this->fromSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet(
                $query->event,
                $fromParticipants
            );
        }

        if ($toHasNoPreference) {
            $toParticipants = $query->request->getToSheet()->getParticipantsArray();

            $this->toSheet->availableSlotsByParticipant = $this->getAvailableSlotsByParticipants(
                $query->event,
                $toParticipants
            );
        } else {
            $this->toSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet(
                $query->event,
                $toParticipants
            );
        }

        $availableMeetings = $this->buildAvailableMeetings();

        $transformableMeeting = $this->getTransformableMeeting($query->event, $availableMeetings);

        // Can throw NoSpotsAvailableForThisSlotAndMeetingException
        $spot = $this->availableSpots->getBySlot(
            $transformableMeeting->slot,
            $transformableMeeting->fromSheet,
            $transformableMeeting->toSheet,
            count($transformableMeeting->toParticipants) + count($transformableMeeting->fromParticipants),
            $query->visio
        );

        $meeting = new Meeting(
            $query->request,
            $transformableMeeting->slot,
            $transformableMeeting->fromSheet,
            $transformableMeeting->fromParticipants,
            $transformableMeeting->toSheet,
            $transformableMeeting->toParticipants,
            $this->dateTime,
            $spot,
            $query->event
        );

        $this->meetingRepository->add($meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$fromSheet, $toSheet])
        );

        foreach ($meeting->getAllParticipants() as $participant) {
            $this->eventDispatcher->dispatch(Events::MEETING_PARTICIPATE,
                new MeetingParticipateEvent($participant)
            );
        }

        return $meeting;
    }

    /**
     * @param Event         $event
     * @param Participant[] $participants
     *
     * @return AvailableSlotsParticipantView[]
     */
    private function getAvailableSlotsByParticipants(Event $event, array $participants): array
    {
        $availableSlotParticipantViews = [];

        foreach ($participants as $participant) {
            $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
                $event,
                [$participant]
            );

            $availableSlotViews = [];

            foreach ($slots as $slot) {
                $availableSlotViews[$slot->getId()] = $slot;
            }

            $availableSlotParticipantViews[$participant->getId()] = new AvailableSlotsParticipantView(
                $participant,
                $availableSlotViews
            );
        }

        return $availableSlotParticipantViews;
    }

    /**
     * @param Event $event
     * @param array $participants
     *
     * @return array
     */
    private function getAvailableSlotsBySheet(Event $event, array $participants): array
    {
        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            $participants
        );

        return $slots;
    }

    /**
     * @return AvailableMeetingView[]
     */
    private function buildAvailableMeetings(): array
    {
        $availableMeetings = [];

        // fromSheet and toSheet has participants preference
        if (!$this->fromSheet->hasNoPreference && !$this->toSheet->hasNoPreference) {
            foreach ($this->fromSheet->availableSlotsBySheet as $slot) {
                if ($this->hasCommonSlot($this->toSheet, $slot)) {
                    $availableMeetings[] = $this->createAvailableMeetingView(
                        $slot,
                        $this->fromSheet->participants,
                        $this->toSheet->participants
                    );
                }
            }
        }

        // fromSheet has participants preference and toSheet has no preference
        if (!$this->fromSheet->hasNoPreference && $this->toSheet->hasNoPreference) {
            foreach ($this->toSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($this->fromSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            $this->fromSheet->participants,
                            [$availableSlotsParticipantView->participant]
                        );
                    }
                }
            }
        }

        if (!$this->toSheet->hasNoPreference && $this->fromSheet->hasNoPreference) {
            foreach ($this->fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($this->toSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            [$availableSlotsParticipantView->participant],
                            $this->toSheet->participants
                        );
                    }
                }
            }
        }

        if ($this->fromSheet->hasNoPreference && $this->toSheet->hasNoPreference) {
            foreach ($this->fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($this->toSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            [$availableSlotsParticipantView->participant],
                            [$this->getParticipantOnAvailableSlot($this->toSheet, $slot)]
                        );
                    }
                }
            }
        }

        return $availableMeetings;
    }

    /**
     * @param AvailableSlotsBySheetView $sheetView
     * @param MeetingSlot               $slot
     *
     * @return bool
     */
    private function hasCommonSlot(AvailableSlotsBySheetView $sheetView, MeetingSlot $slot): bool
    {
        if (!$sheetView->hasNoPreference) {
            foreach ($sheetView->availableSlotsBySheet as $otherSlot) {
                if ($otherSlot->getId() === $slot->getId()) {
                    return true;
                }
            }

            return false;
        }

        foreach ($sheetView->availableSlotsByParticipant as $availableSlotsParticipantView) {
            if (isset($availableSlotsParticipantView->slots[$slot->getId()])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AvailableSlotsBySheetView $sheetView
     * @param MeetingSlot               $slot
     *
     * @return null|Participant
     */
    private function getParticipantOnAvailableSlot(
        AvailableSlotsBySheetView $sheetView,
        MeetingSlot $slot
    ): ?Participant
    {
        foreach ($sheetView->availableSlotsByParticipant as $availableSlotsParticipantView) {
            if (isset($availableSlotsParticipantView->slots[$slot->getId()])) {
                return $availableSlotsParticipantView->participant;
            }
        }

        return null;
    }

    /**
     * @param MeetingSlot $slot
     * @param array       $fromParticipants
     * @param array       $toParticipants
     *
     * @return AvailableMeetingView
     */
    private function createAvailableMeetingView(
        MeetingSlot $slot,
        array $fromParticipants,
        array $toParticipants
    ): AvailableMeetingView {
        return new AvailableMeetingView(
            $slot,
            $this->fromSheet->sheet,
            $this->toSheet->sheet,
            $fromParticipants,
            $toParticipants
        );

    }

    /**
     * @param Event                  $event
     * @param AvailableMeetingView[] $availableMeetings
     *
     * @return AvailableMeetingView
     */
    private function getTransformableMeeting(Event $event, array $availableMeetings): AvailableMeetingView
    {
        foreach ($availableMeetings as $availableMeeting) {
            $fromParticipant = $this->participantWithPhoneValidated->getParticipant(
                $event,
                $availableMeeting->fromSheet,
                $availableMeeting->fromParticipants
            );

            $toParticipant = $this->participantWithPhoneValidated->getParticipant(
                $event,
                $availableMeeting->toSheet,
                $availableMeeting->toParticipants
            );

            if ($fromParticipant !== null || $toParticipant !== null) {
                return $availableMeeting;
            }
        }

        return reset($availableMeetings);
    }
}
