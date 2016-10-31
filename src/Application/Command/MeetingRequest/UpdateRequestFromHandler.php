<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class UpdateRequestFromHandler
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
    private $datetime;

    /**
     * EditRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $datetime
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->datetime          = $datetime;
    }

    /**
     * @param UpdateRequestFrom $updateRequestFrom
     */
    public function handle(UpdateRequestFrom $updateRequestFrom)
    {
        if (!$updateRequestFrom->meetingRequest->getFromSheet()->hasUser($updateRequestFrom->editor)) {
            throw new \RuntimeException('You are not allowed to update this request.');
        }

        // Remove removed participants
        foreach ($updateRequestFrom->meetingRequest->getFromParticipants() as $participant) {
            if (!in_array($participant, $updateRequestFrom->participants)) {
                $updateRequestFrom->meetingRequest->removeFromParticipant($participant);
            }
        }

        // Add new participants
        foreach ($updateRequestFrom->participants as $participant) {
            if (!$updateRequestFrom->meetingRequest->hasFromParticipant($participant)) {
                $updateRequestFrom->meetingRequest->addFromParticipant($participant);
            }
        }

        // Save request
        $this->requestRepository->set($updateRequestFrom->meetingRequest);

        // Add message
        if ($updateRequestFrom->description) {
            $message = new Message(
                $updateRequestFrom->meetingRequest,
                $updateRequestFrom->meetingRequest->getFromSheet(),
                $updateRequestFrom->description,
                $this->datetime
            );

            $this->messageRepository->add($message);
        }
    }
}
