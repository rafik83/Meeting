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
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class PositionMeetingHandler
{
    private $meetingRepository;

    private $requestRepository;

    public function __construct(MeetingRepositoryInterface $meetingRepository, RequestRepositoryInterface $requestRepository)
    {
        $this->meetingRepository = $meetingRepository;
        $this->requestRepository = $requestRepository;
    }

    public function handle(PositionMeeting $positionMeeting)
    {
        // Create the meeting
        $meeting = new Meeting(
            $positionMeeting->slot,
            $positionMeeting->fromSheet,
            $positionMeeting->fromParticipants,
            $positionMeeting->toSheet,
            $positionMeeting->toParticipants
        );

        $this->meetingRepository->add($meeting);

        // Attach the meeting to the request
        $positionMeeting->meetingRequest->setMeeting($meeting);

        $this->requestRepository->set($positionMeeting->meetingRequest);
    }
}
