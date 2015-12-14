<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Application\Exception\MeetingRequest\SlotUnavailableException;

class PositionMeetingHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * PositionMeetingHandler constructor.
     *
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->meetingRepository     = $meetingRepository;
        $this->requestRepository     = $requestRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param PositionMeeting $positionMeeting
     */
    public function handle(PositionMeeting $positionMeeting)
    {
        $this->checkAvailability($positionMeeting);

        // Create the meeting
        $meeting = new Meeting(
            $positionMeeting->slot,
            $positionMeeting->fromSheet,
            $positionMeeting->fromParticipants,
            $positionMeeting->toSheet,
            $positionMeeting->toParticipants,
            $positionMeeting->createdAt
        );

        $this->meetingRepository->add($meeting);

        // Attach the meeting to the request
        $positionMeeting->meetingRequest->setMeeting($meeting);

        $this->requestRepository->set($positionMeeting->meetingRequest);
    }

    /**
     * @param PositionMeeting $positionMeeting
     *
     * @throws SlotUnavailableException
     */
    private function checkAvailability(PositionMeeting $positionMeeting)
    {
        $ids = array_map(function (Participant $participant) {
            return $participant->getId();
        }, array_merge($positionMeeting->fromParticipants, $positionMeeting->toParticipants));

        $slots = $this->meetingSlotRepository->findAvailableSlotIdByParticipantsIds($ids);

        if (!in_array($positionMeeting->slot->getId(), $slots)) {
            throw new SlotUnavailableException(
                sprintf('The slot %d is not available.', $positionMeeting->slot->getId())
            );
        }
    }
}
