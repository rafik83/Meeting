<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class TransformRequestIntoMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    public $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    public $eventDispatcher;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var AvailableSpots */
    private $availableSpots;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SlotFilter */
    private $slotFilter;

    /** @var VisioGuesser */
    private $visioGuesser;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /**
     * TransformRequestIntoMeetingHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param UserEventPhoneChecker           $userEventPhoneChecker
     * @param AvailableSpots                  $availableSpots
     * @param MeetingRepositoryInterface      $meetingRepository
     * @param SlotFilter                      $slotFilter
     * @param VisioGuesser                    $visioGuesser
     * @param DelayedEventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface              $dateTime
     * @param MeetingParticipants             $meetingParticipants
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        UserEventPhoneChecker $userEventPhoneChecker,
        AvailableSpots $availableSpots,
        MeetingRepositoryInterface $meetingRepository,
        SlotFilter $slotFilter,
        VisioGuesser $visioGuesser,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime,
        MeetingParticipants $meetingParticipants
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->userEventPhoneChecker = $userEventPhoneChecker;
        $this->availableSpots        = $availableSpots;
        $this->dateTime              = $dateTime;
        $this->meetingRepository     = $meetingRepository;
        $this->eventDispatcher       = $eventDispatcher;
        $this->slotFilter            = $slotFilter;
        $this->visioGuesser          = $visioGuesser;
        $this->meetingParticipants = $meetingParticipants;
    }

    /**
     * @param TransformRequestIntoMeeting $query
     *
     * @throws CannotBeTransformIntoMeetingOnDdayException
     *
     * @return Meeting
     */
    public function handle(TransformRequestIntoMeeting $query): Meeting
    {
        $fromSheet = $query->request->getFromSheet();
        $toSheet = $query->request->getToSheet();

        $fromIsNoPreference = $fromSheet->getType()->areAllSheetParticipantsAssignedToMeeting()
            ? false
            : $query->request->hasNoPreference($fromSheet);

        $toIsNoPreference = $toSheet->getType()->areAllSheetParticipantsAssignedToMeeting()
            ? false
            : $query->request->hasNoPreference($toSheet);

        $fromParticipants = $this->meetingParticipants->getMeetingParticipants($query->request, $fromSheet);
        $toParticipants = $this->meetingParticipants->getMeetingParticipants($query->request, $toSheet);

        if ($fromIsNoPreference && 1 === $fromSheet->countParticipants()) {
            $fromIsNoPreference = false;
            $fromParticipants = $fromSheet->getParticipantsArray();
        }

        if ($toIsNoPreference && 1 === $toSheet->countParticipants()) {
            $toIsNoPreference = false;
            $toParticipants = $toSheet->getParticipantsArray();
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

        if (0 === count($availableMeetings)) {
            throw new CannotBeTransformIntoMeetingOnDdayException();
        }

        $transformableMeeting = $this->getTransformableMeeting($query->event, $availableMeetings);

        foreach ($transformableMeeting->fromParticipants as $fromParticipant) {
            if (!in_array($fromParticipant, $fromParticipants, true)) {
                throw new \LogicException('Chosen From participant is invalid for this meeting');
            }
        }

        foreach ($transformableMeeting->toParticipants as $toParticipant) {
            if (!in_array($toParticipant, $toParticipants, true)) {
                throw new \LogicException('Chosen To participant is invalid for this meeting');
            }
        }

        if (!$transformableMeeting->spot instanceof Spot) {
            throw new \LogicException('Spot is required');
        }

        $meeting = new Meeting(
            $query->request,
            $transformableMeeting->slot,
            $transformableMeeting->fromSheet,
            $transformableMeeting->fromParticipants,
            $transformableMeeting->toSheet,
            $transformableMeeting->toParticipants,
            $this->dateTime,
            $transformableMeeting->spot,
            $query->event,
            false,
            false,
            Meeting::CREATED_BY_PARTICIPANT
        );

        $this->meetingRepository->add($meeting);
        $query->request->setMeeting($meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent($meeting)
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

        return $this->slotFilter->getFilteredSlots($slots);
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
                        false,
                        false
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
                            false,
                            true
                        );
                    }
                }
            }

            return $availableMeetings;
        }

        // fromSheet has no preference and toSheet has participants preference
        if ($fromSheet->hasNoPreference && !$toSheet->hasNoPreference) {
            foreach ($fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($toSheet, $slot)) {
                        $availableMeetings[] = $this->createAvailableMeetingView(
                            $slot,
                            $fromSheet->sheet,
                            $toSheet->sheet,
                            [$availableSlotsParticipantView->participant],
                            $toSheet->participants,
                            true,
                            false
                        );
                    }
                }
            }

            return $availableMeetings;
        }

        // No preference on both side
        if ($fromSheet->hasNoPreference && $toSheet->hasNoPreference) {
            foreach ($fromSheet->availableSlotsByParticipant as $fromAvailableSlotsParticipantView) {
                foreach ($toSheet->availableSlotsByParticipant as $toAvailableSlotsParticipantView) {
                    foreach ($fromAvailableSlotsParticipantView->slots as $fromSlot) {
                        if (in_array($fromSlot, $toAvailableSlotsParticipantView->slots, true)) {
                            $availableMeetings[] = $this->createAvailableMeetingView(
                                $fromSlot,
                                $fromSheet->sheet,
                                $toSheet->sheet,
                                [$fromAvailableSlotsParticipantView->participant],
                                [$toAvailableSlotsParticipantView->participant],
                                true,
                                true
                            );
                        }
                    }
                }
            }

            return $availableMeetings;
        }

        return [];
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
     * @throws CannotBeTransformIntoMeetingOnDdayException
     *
     * @return AvailableMeetingView
     */
    private function getTransformableMeeting(Event $event, array $availableMeetings): AvailableMeetingView
    {
        foreach ($availableMeetings as $index => $availableMeeting) {
            if ($availableMeeting->fromSheetHasNoPreference) {
                $availableMeeting->fromParticipantIsPhoneValidated = $this->userEventPhoneChecker->isValidated(
                    $availableMeeting->getFromParticipant()->getUser(),
                    $event
                );
            }

            if ($availableMeeting->toSheetHasNoPreference) {
                $availableMeeting->toParticipantIsPhoneValidated = $this->userEventPhoneChecker->isValidated(
                    $availableMeeting->getToParticipant()->getUser(),
                    $event
                );
            }

            $spot = $this->getSpotForAvailableMeeting($availableMeeting);

            if (!$spot instanceof Spot) {
                // this meeting can not have a spot, remove it
                unset($availableMeetings[$index]);

                continue;
            }

            $availableMeeting->spot = $spot;

            // The both side have a phone validated
            if (true === $availableMeeting->fromParticipantIsPhoneValidated
                && true === $availableMeeting->toParticipantIsPhoneValidated
            ) {
                return $availableMeeting;
            }
        }

        // I accept the proposition (I'm am the "to" participant)
        // if the "from" participant has a phone validated, the meeting is created
        foreach ($availableMeetings as $availableMeeting) {
            if (true === $availableMeeting->fromParticipantIsPhoneValidated) {
                return $availableMeeting;
            }
        }

        // Create meeting if to participant has validated phone
        foreach ($availableMeetings as $availableMeeting) {
            if (true === $availableMeeting->toParticipantIsPhoneValidated) {
                return $availableMeeting;
            }
        }

        // default meeting, the first one with a spot
        $availableMeeting = reset($availableMeetings);

        if (false === $availableMeeting) {
            throw new CannotBeTransformIntoMeetingOnDdayException();
        }

        return $availableMeeting;
    }

    /**
     * @param AvailableMeetingView $availableMeetingView
     *
     * @return null|Spot
     */
    private function getSpotForAvailableMeeting(AvailableMeetingView $availableMeetingView)
    {
        try {
            return $this->availableSpots->getBySlot(
                $availableMeetingView->slot,
                $availableMeetingView->fromSheet,
                $availableMeetingView->toSheet,
                $availableMeetingView->getTotalParticipants(),
                $this->visioGuesser->isParticipantVisio(
                    array_merge($availableMeetingView->fromParticipants, $availableMeetingView->toParticipants)
                )
            );
        } catch (NoSpotsAvailableForThisSlotAndMeetingException $exception) {
            return null;
        }
    }
}
