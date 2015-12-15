<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class UpdateHandler
{
    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param \DateTimeInterface         $dateTime
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(\DateTimeInterface $dateTime, RequestRepositoryInterface $requestRepository)
    {
        $this->dateTime          = $dateTime;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        // Modifying a meeting amounts to creating a new meeting request linked to this meeting
        // When the new meeting request is accepted, the meeting is updated else it isn't.

        $request = new Request(
            $update->meeting->getFrom(),
            $update->fromParticipants->toArray(),
            $update->meeting->getTo(),
            $update->toParticipants->toArray(),
            $update->message,
            $this->dateTime
        );

        $request->setMeeting($update->meeting);
        $request->setMeetingSlot($update->meetingSlot);

        $this->requestRepository->add($request);
    }
}
