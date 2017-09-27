<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\CannotBeTransformIntoMeetingOnDdayException;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableMeetingView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsBySheetView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantWithPhoneValidated;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;

class TransformRequestIntoMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    public $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    public $eventDispatcher;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ParticipantWithPhoneValidated */
    private $participantWithPhoneValidated;

    /** @var AvailableSpots */
    private $availableSpots;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SlotFilter */
    private $slotFilter;

    /**
     * TransformRequestIntoMeetingHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param ParticipantWithPhoneValidated   $participantWithPhoneValidated
     * @param AvailableSpots                  $availableSpots
     * @param MeetingRepositoryInterface      $meetingRepository
     * @param SlotFilter                      $slotFilter
     * @param DelayedEventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        ParticipantWithPhoneValidated $participantWithPhoneValidated,
        AvailableSpots $availableSpots,
        MeetingRepositoryInterface $meetingRepository,
        SlotFilter $slotFilter,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingSlotRepository         = $meetingSlotRepository;
        $this->participantWithPhoneValidated = $participantWithPhoneValidated;
        $this->availableSpots                = $availableSpots;
        $this->dateTime                      = $dateTime;
        $this->meetingRepository             = $meetingRepository;
        $this->eventDispatcher               = $eventDispatcher;
        $this->slotFilter                    = $slotFilter;
    }

    /**
     * @param TransformRequestIntoMeeting $query
     *
     * @return Meeting
     * @throws CannotBeTransformIntoMeetingOnDdayException
     */
    public function handle(TransformRequestIntoMeeting $query): Meeting
    {
        $fromSheet           = $query->request->getFromSheet();
        $toSheet             = $query->request->getToSheet();
        $fromParticipants    = $query->request->getFromParticipantsArray();
        $toParticipants      = $query->request->getToParticipantsArray();
        $fromIsNoPreference = $query->request->hasNoPreference($fromSheet);
        $toIsNoPreference   = $query->request->hasNoPreference($toSheet);

        if ($fromIsNoPreference && 1 === $fromSheet->countParticipants()) {
            $fromIsNoPreference = false;
            $fromParticipants    = $fromSheet->getParticipantsArray();
        }

        if ($toIsNoPreference && 1 === $toSheet->countParticipants()) {
            $toIsNoPreference = false;
            $toParticipants    = $fromSheet->getParticipantsArray();
        }

        $fromSheet = new AvailableSlotsBySheetView($fromSheet, $fromIsNoPreference);
        $toSheet   = new AvailableSlotsBySheetView($toSheet, $toIsNoPreference);

        if ($fromIsNoPreference) {
            $fromParticipants = $query->request->getFromSheet()->getParticipantsArray();

            $fromSheet->availableSlotsByParticipant = $this->getAvailableSlotsByParticipants(
                $query->event,
                $fromParticipants
            );
        } else {
            $fromSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet(
                $query->event,
                $fromParticipants
            );
        }

        if ($toIsNoPreference) {
            $toParticipants = $query->request->getToSheet()->getParticipantsArray();

            $toSheet->availableSlotsByParticipant = $this->getAvailableSlotsByParticipants(
                $query->event,
                $toParticipants
            );
        } else {
            $toSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet(
                $query->event,
                $toParticipants
            );
        }

        $fromSheet->setParticipants($fromParticipants);
        $toSheet->setParticipants($toParticipants);

        $availableMeetings = $this->buildAvailableMeetings($fromSheet, $toSheet);

        if (count($availableMeetings) === 0) {
            throw new CannotBeTransformIntoMeetingOnDdayException();
        }

        $transformableMeeting = $this->getTransformableMeeting($query->event, $availableMeetings);

        try {
            $spot = $this->availableSpots->getBySlot(
                $transformableMeeting->slot,
                $transformableMeeting->fromSheet,
                $transformableMeeting->toSheet,
                $transformableMeeting->getTotalParticipants(),
                $query->visio
            );
        } catch (NoSpotsAvailableForThisSlotAndMeetingException $exception) {
            throw new CannotBeTransformIntoMeetingOnDdayException();
        }

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
            new MeetingCreatedEvent([$fromSheet->sheet, $toSheet->sheet])
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

            $slots = $this->slotFilter->getFilteredSlots($slots);

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

        $slots = $this->slotFilter->getFilteredSlots($slots);

        return $slots;
    }

    /**
     * @param AvailableSlotsBySheetView $fromSheet
     * @param AvailableSlotsBySheetView $toSheet
     *
     * @return array
     */
    private function buildAvailableMeetings(
        AvailableSlotsBySheetView $fromSheet,
        AvailableSlotsBySheetView $toSheet
    ): array {
        $availableMeetings = [];

        // fromSheet and toSheet has participants preference
        if (!$fromSheet->hasNoPreference && !$toSheet->hasNoPreference) {
            foreach ($fromSheet->availableSlotsBySheet as $slot) {
                if ($this->hasCommonSlot($toSheet, $slot)) {
                    $availableMeetings[] = $this->createAvailableMeetingView(
                        $slot,
                        $fromSheet->sheet,
                        $toSheet->sheet,
                        $fromSheet->participants,
                        $toSheet->participants,
                        $fromSheet->hasNoPreference,
                        $toSheet->hasNoPreference
                    );
                }
            }

            return $availableMeetings;
        }

        // fromSheet has participants preference and toSheet has no preference
        if (!$fromSheet->hasNoPreference && $toSheet->hasNoPreference) {
            foreach ($toSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($fromSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            $fromSheet->sheet,
                            $toSheet->sheet,
                            $fromSheet->participants,
                            [$availableSlotsParticipantView->participant],
                            $fromSheet->hasNoPreference,
                            $toSheet->hasNoPreference
                        );
                    }
                }
            }

            return $availableMeetings;
        }

        if (!$toSheet->hasNoPreference && $fromSheet->hasNoPreference) {
            foreach ($fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($toSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            $fromSheet->sheet,
                            $toSheet->sheet,
                            [$availableSlotsParticipantView->participant],
                            $toSheet->participants,
                            $fromSheet->hasNoPreference,
                            $toSheet->hasNoPreference
                        );
                    }
                }
            }

            return $availableMeetings;
        }

        if ($fromSheet->hasNoPreference && $toSheet->hasNoPreference) {
            foreach ($fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($toSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            $fromSheet->sheet,
                            $toSheet->sheet,
                            [$availableSlotsParticipantView->participant],
                            [$this->getParticipantOnAvailableSlot($toSheet, $slot)],
                            $fromSheet->hasNoPreference,
                            $toSheet->hasNoPreference
                        );
                    }
                }
            }

            return $availableMeetings;
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
     * @param Sheet       $fromSheet
     * @param Sheet       $toSheet
     * @param array       $fromParticipants
     * @param array       $toParticipants
     * @param bool        $fromSheetHasNoPreference
     * @param bool        $toSheetHasNoPreference
     *
     * @return AvailableMeetingView
     */
    private function createAvailableMeetingView(
        MeetingSlot $slot,
        Sheet $fromSheet,
        Sheet $toSheet,
        array $fromParticipants,
        array $toParticipants,
        bool $fromSheetHasNoPreference,
        bool $toSheetHasNoPreference
    ): AvailableMeetingView {
        return new AvailableMeetingView(
            $slot,
            $fromSheet,
            $toSheet,
            $fromParticipants,
            $toParticipants,
            $fromSheetHasNoPreference,
            $toSheetHasNoPreference
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
            if ($availableMeeting->fromSheetHasNoPreference) {
                $fromParticipant = $this->participantWithPhoneValidated->getParticipant(
                    $event,
                    $availableMeeting->fromParticipants
                );
            }

            if ($availableMeeting->toSheetHasNoPreference) {
                $toParticipant = $this->participantWithPhoneValidated->getParticipant(
                    $event,
                    $availableMeeting->toParticipants
                );
            }

            if (!empty($fromParticipant) || !empty($toParticipant)) {
                return $availableMeeting;
            }
        }

        return reset($availableMeetings);
    }
}
