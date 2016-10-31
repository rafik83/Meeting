<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class UpdateRequestToHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * EditRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param UpdateRequestTo $updateRequestTo
     */
    public function handle(UpdateRequestTo $updateRequestTo)
    {
        if (!$updateRequestTo->meetingRequest->getToSheet()->hasUser($updateRequestTo->editor)) {
            throw new \RuntimeException('You are not allowed to update this request.');
        }

        // Remove removed participants
        foreach ($updateRequestTo->meetingRequest->getToParticipants() as $participant) {
            if (!in_array($participant, $updateRequestTo->participants)) {
                $updateRequestTo->meetingRequest->removeToParticipant($participant);
            }
        }

        // Add new participants
        foreach ($updateRequestTo->participants as $participant) {
            if (!$updateRequestTo->meetingRequest->hasToParticipant($participant)) {
                $updateRequestTo->meetingRequest->addToParticipant($participant);
            }
        }

        // Save request
        $this->requestRepository->set($updateRequestTo->meetingRequest);

        // Add message
        if ($updateRequestTo->description) {
            $message = new Message(
                $updateRequestTo->meetingRequest,
                $updateRequestTo->meetingRequest->getToSheet(),
                $updateRequestTo->description,
                $this->dateTime
            );

            $this->messageRepository->add($message);
        }
    }
}
