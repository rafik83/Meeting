<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableMeetingView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsBySheetView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotsParticipantView;
use Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting\AvailableSlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class TransformRequestIntoMeetingHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

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
     * TransformRequestIntoMeetingHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param TransformRequestIntoMeeting $query
     */
    public function handle(TransformRequestIntoMeeting $query)
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
            $fromParticipants = $query->request->getFromSheet()->getParticipants()->toArray();

            $this->fromSheet->availableSlotsByParticipant = $this->getAvailableSlots($query->event, $fromParticipants);
        } else {
            $this->fromSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet($query->event, $fromParticipants);
        }

        if ($toHasNoPreference) {
            $toParticipants = $query->request->getToSheet()->getParticipants()->toArray();

            $this->toSheet->availableSlotsByParticipant = $this->getAvailableSlots($query->event, $toParticipants);
        } else {
            $this->toSheet->availableSlotsBySheet = $this->getAvailableSlotsBySheet($query->event, $toParticipants);
        }
    }

    /**
     * @param Event         $event
     * @param Participant[] $participants
     *
     * @return AvailableSlotsParticipantView[]
     */
    private function getAvailableSlots(Event $event, array $participants): array
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

    private function generateAvailableMeeting()
    {
        $availableMeetings = [];
        $fromParticipants = [];
        $toParticipants = [];

        if ($this->fromSheet->hasPreference() && $this->toSheet->hasPreference()) {
            foreach ($this->fromSheet->availableSlotsBySheet as $slot) {
                if ($this->hasCommonSlot($this->toSheet, $slot)) {
                    $availableMeetings[] = new AvailableMeetingView(
                        $slot,
                        $this->fromSheet->sheet,
                        $this->toSheet->sheet,
                        $this->fromSheet->participants,
                        $this->toSheet->participants
                    );
                }
            }
        }

        if ($this->fromSheet->hasPreference()) {
            if ($this->toSheet->hasNoPreference) {
                foreach ($this->toSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                    foreach ($availableSlotsParticipantView->slots as $slot) {
                        if ($this->hasCommonSlot($this->fromSheet, $slot)) {
                            $availableMeetings[] = new AvailableMeetingView(
                                $slot,
                                $this->fromSheet->sheet,
                                $this->toSheet->sheet,
                                $this->fromSheet->participants,
                                [$availableSlotsParticipantView->participant]
                            );
                        }
                    }
                }
            }
        }

        if ($this->toSheet->hasPreference()) {
            if ($this->fromSheet->hasNoPreference) {
                foreach ($this->fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                    foreach ($availableSlotsParticipantView->slots as $slot) {
                        if ($this->hasCommonSlot($this->toSheet, $slot)) {
                            $availableMeetings[] = new AvailableMeetingView(
                                $slot,
                                $this->fromSheet->sheet,
                                $this->toSheet->sheet,
                                [$availableSlotsParticipantView->participant],
                                $this->toSheet->participants
                            );
                        }
                    }
                }
            }
        }

        if ($this->fromSheet->hasNoPreference && $this->toSheet->hasNoPreference) {
            foreach ($this->fromSheet->availableSlotsByParticipant as $availableSlotsParticipantView) {
                foreach ($availableSlotsParticipantView->slots as $slot) {
                    if ($this->hasCommonSlot($this->toSheet, $slot)) {
                        $availableMeetings[] = new AvailableMeetingView(
                            $slot,
                            $this->fromSheet->sheet,
                            $this->toSheet->sheet,
                            [$availableSlotsParticipantView->participant],
                            [$this->getParticipantOnAvailableSlot($this->toSheet, $slot)]
                        );
                    }
                }
            }
        }
    }

    /**
     * @param AvailableSlotsBySheetView $sheetView
     * @param MeetingSlot               $slot
     *
     * @return bool
     */
    private function hasCommonSlot(AvailableSlotsBySheetView $sheetView, MeetingSlot $slot): bool
    {
        if ($sheetView->hasPreference()) {
            if (isset($sheetView->availableSlotsBySheet[$slot->getId()])) {
                return true;
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
}
