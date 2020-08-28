<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class MeetingManager
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantManager */
    private $participantManager;

    /** @var SlotManager */
    private $slotManager;

    /** @var SpotManager */
    private $spotManager;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetRepositoryInterface   $sheetRepository
     * @param ParticipantManager         $participantManager
     * @param SlotManager                $slotManager
     * @param SpotManager                $spotManager
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantManager $participantManager,
        SlotManager $slotManager,
        SpotManager $spotManager
    ) {
        $this->meetingRepository  = $meetingRepository;
        $this->requestRepository  = $requestRepository;
        $this->participantManager = $participantManager;
        $this->slotManager        = $slotManager;
        $this->spotManager        = $spotManager;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event       $event
     * @param Request     $meetingRequest
     * @param MeetingSlot $slot
     * @param Spot        $spot
     *
     * @return Meeting
     */
    public function createMeetingFromRequest(Event $event, Request $meetingRequest, MeetingSlot $slot, Spot $spot)
    {
        $meeting = new Meeting(
            $meetingRequest,
            $slot,
            $meetingRequest->getFromSheet(),
            $meetingRequest->getFromParticipants()->toArray(),
            $meetingRequest->getToSheet(),
            $meetingRequest->getToParticipants()->toArray(),
            new \DateTime(),
            $spot,
            $event,
            false,
            false
        );

        $this->meetingRepository->add($meeting);

        return $meeting;
    }

    /**
     * @param Event         $event
     * @param Sheet|null    $fromSheet
     * @param Participant[] $fromParticipants
     * @param Sheet|null    $toSheet
     * @param Participant[] $toParticipants
     *
     * @return Request
     */
    public function createMeetingRequest(
        Event $event,
        Sheet $fromSheet = null,
        array $fromParticipants = [],
        Sheet $toSheet = null,
        array $toParticipants = []
    ) {
        if (empty($fromParticipants)) {
            $fromParticipants = [$this->participantManager->create($event, $fromSheet)];
        }

        if (empty($toParticipants)) {
            $toParticipants = [$this->participantManager->create($event, $toSheet)];
        }

        $firstFromParticipant = reset($fromParticipants);
        $fromSheet = $firstFromParticipant->getSheet();

        $firstToParticipant = reset($toParticipants);
        $toSheet = $firstToParticipant->getSheet();

        $meetingRequest = new Request(
            $fromSheet,
            $fromParticipants,
            $toSheet,
            $toParticipants,
            new \DateTime(),
            $firstFromParticipant->getUser(),
            $event
        );

        $this->requestRepository->add($meetingRequest);

        return $meetingRequest;
    }

    /**
     * @param Event  $event
     * @param string $spotReference
     *
     * @throws \Exception
     *
     * @return Meeting
     */
    public function createMeetingOnSpot(Event $event, $spotReference)
    {
        $spot = $this->spotManager->getByReference($event, $spotReference);

        if (null === $spot) {
            throw new \Exception('Spot not found');
        }

        $meetingRequest = $this->createMeetingRequest($event);

        $slots = $this->slotManager->findByEvent($event);
        $slot  = reset($slots);

        if (false === $slot) {
            throw new \Exception('There are no available slot for this meeting');
        }

        return $this->createMeetingFromRequest($event, $meetingRequest, $slot, $spot);
    }

    /**
     * @param Event $event
     * @param int   $slotId
     *
     * @throws \Exception
     *
     * @return Meeting
     */
    public function createMeetingOnSlot(Event $event, $slotId)
    {
        $slot = $this->slotManager->findByEventAndId($event, $slotId);

        if (null === $slot) {
            throw new \Exception('Slot not found');
        }

        $meetingRequest = $this->createMeetingRequest($event);

        $spot = $this->spotManager->create($event, 'MyRef', 1, 2, true);

        return $this->createMeetingFromRequest($event, $meetingRequest, $slot, $spot);
    }

    public function createMeetingSlot(Event $event, $fromDate, $toDate): MeetingSlot
    {
        $slot = new MeetingSlot($event, $fromDate, $toDate);
        $this->slotManager->addSlot($slot);

        return $slot;
    }

    /**
     * @param Participant $participant
     * @param int         $slotId
     * @param string      $spotReference
     *
     * @throws \Exception
     *
     * @return Meeting
     */
    public function createMeetingForParticipantOnGivenSlotAndSpot(Participant $participant, $slotId, $spotReference)
    {
        $sheet = $participant->getSheet();
        $event = $sheet->getEvent();
        $slot  = $this->slotManager->findByEventAndId($event, $slotId);

        if (null === $slot) {
            throw new \Exception('Slot not found');
        }

        $meetingRequest = $this->createMeetingRequest($event, $sheet, [$participant]);

        $spot = $this->spotManager->getByReference($event, $spotReference);

        if (null === $spot) {
            throw new \Exception('Spot not found');
        }

        return $this->createMeetingFromRequest($event, $meetingRequest, $slot, $spot);
    }

    /**
     * @param Event  $event
     * @param string $sheetTitle
     * @param string $otherSheetTitle
     * @param string $spotReference
     *
     * @throws \Exception
     *
     * @return Meeting
     */
    public function createMeetingForSheetsAndSpot(Event $event, $sheetTitle, $otherSheetTitle, $spotReference)
    {
        $sheet = $this->sheetRepository->getSheetByEventAndTitle($event, $sheetTitle);
        $otherSheet = $this->sheetRepository->getSheetByEventAndTitle($event, $otherSheetTitle);

        if (null === $sheet || null === $otherSheet) {
            throw new \InvalidArgumentException('Missing sheet');
        }

        $meetingRequest = $this->createMeetingRequest(
            $event,
            $sheet,
            [$sheet->getFirstParticipant()],
            $otherSheet,
            [$otherSheet->getFirstParticipant()]
        );

        $spot = $this->spotManager->getByReference($event, $spotReference);

        if (null === $spot) {
            throw new \Exception('Spot not found');
        }

        $slots = $this->slotManager->findByEvent($event);
        $slot  = reset($slots);

        if (false === $slot) {
            throw new \Exception('There are no available slot for this meeting');
        }

        $meeting = $this->createMeetingFromRequest($event, $meetingRequest, $slot, $spot);

        return $meeting;
    }

    public function createVideoMeetingForParticipant(Event $event, Participant $participant)
    {
        $sheet = $participant->getSheet();
        $this->slotManager->create($event, 1);
        $slots = $this->slotManager->findByEvent($event);
        $slot = reset($slots);

        if (false === $slot) {
            throw new \Exception('Meeting Slot not found');
        }

        $meetingRequest = $this->createMeetingRequest($event, $sheet, [$participant]);

        $spotReference = 'Visio1';
        $this->spotManager->create($event, $spotReference, 1, 10, true, true, true);
        $spot = $this->spotManager->getByReference($event, $spotReference);

        if (null === $spot || !$spot->isVisio()) {
            throw new \Exception('Spot not found');
        }

        return $this->createMeetingFromRequest($event, $meetingRequest, $slot, $spot);
    }
}
