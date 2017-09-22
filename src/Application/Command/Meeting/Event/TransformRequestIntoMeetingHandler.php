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
use Proximum\Vimeet\Domain\Model\Sheet;
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
    private $fromSheetAvailableSlots;

    /**
     * Array of available slots by sheet and by participant
     *
     * @var AvailableSlotsBySheetView
     */
    private $toSheetAvailableSlots;

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
        $fromParticipants    = $query->request->getFromParticipants();
        $toParticipants      = $query->request->getToParticipants();
        $fromHasNoPreference = $query->request->hasNoPreference($fromSheet);
        $toHasNoPreference   = $query->request->hasNoPreference($toSheet);

        if ($fromHasNoPreference) {
            $fromParticipants = $query->request->getFromSheet()->getParticipants()->toArray();
        }

        if ($toHasNoPreference) {
            $toParticipants = $query->request->getToSheet()->getParticipants()->toArray();
        }

        $this->fromSheetAvailableSlots = new AvailableSlotsBySheetView($fromSheet, $fromHasNoPreference);
        $this->toSheetAvailableSlots   = new AvailableSlotsBySheetView($toSheet, $toHasNoPreference);

        $this->fromSheetAvailableSlots->participants = $this->getAvailableSlots($query->event, $fromParticipants);
        $this->toSheetAvailableSlots->participants   = $this->getAvailableSlots($query->event, $toParticipants);
    }

    /**
     * @param Event         $event
     * @param Participant[] $participants
     *
     * @return AvailableSlotsParticipantView[]
     */
    private function getAvailableSlots(Event $event, array $participants): array
    {
        $availableSlotViews = [];

        foreach ($participants as $participant) {
            $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
                $event,
                [$participant]
            );

            $availableSlotViews = [];

            foreach ($slots as $slot) {
                $availableSlotViews[$slot->getId()] = new AvailableSlotView($slot);
            }

            $availableSlotViews[$participant->getId()] = new AvailableSlotsParticipantView(
                $participant,
                $availableSlotViews
            );
        }

        return $availableSlotViews;
    }

    /**
     * @param Sheet $fromSheet
     * @param Sheet $toSheet
     *
     * @return AvailableMeetingView[]
     */
    private function getAvailableMeeting(Sheet $fromSheet, Sheet $toSheet): array
    {
        $availableMeetingViews = [];

        foreach ($this->fromSheetAvailableSlots->participants as $participantView) {
            foreach ($participantView->slots as $slotView) {

                if ($this->hasToSheetCommonSlot($slotView->slot)) {
                    $availableMeetingViews[] = new AvailableMeetingView(
                        $slotView->slot,
                        $fromSheet,
                        $toSheet,
                        [],
                        []
                    );
                }
            }
        }

        return $availableMeetingViews;
    }

    /**
     * @param MeetingSlot $slot
     *
     * @return bool
     */
    private function hasToSheetCommonSlot(MeetingSlot $slot): bool
    {
        foreach ($this->toSheetAvailableSlots->participants as $participant) {
            if (isset($participant->slots[$slot->getId()])) {
                return true;
            }
        }

        return false;
    }
}
