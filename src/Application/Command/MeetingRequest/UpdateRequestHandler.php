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

class UpdateRequestHandler
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
     * @param UpdateRequest $updateRequest
     */
    public function handle(UpdateRequest $updateRequest)
    {
        if (!$updateRequest->meetingRequest->getFromSheet()->hasUser($updateRequest->editor)) {
            throw new \RuntimeException('You are not allowed to update this request.');
        }

        $this->handleAddRemoveParticipant($updateRequest);

        // Save request
        $this->requestRepository->set($updateRequest->meetingRequest);

        // Add message
        if ($updateRequest->description) {
            $message = new Message(
                $updateRequest->meetingRequest,
                $updateRequest->sheetEditor,
                $updateRequest->description,
                $this->datetime
            );

            $this->messageRepository->add($message);
        }
    }

    /**
     * @param UpdateRequest $updateRequest
     */
    private function handleAddRemoveParticipant(UpdateRequest $updateRequest)
    {
        if ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getFromSheet()) {
            // Remove removed participants
            foreach ($updateRequest->meetingRequest->getFromParticipants() as $participant) {
                if (!in_array($participant, $updateRequest->participants)) {
                    $updateRequest->meetingRequest->removeFromParticipant($participant);
                }
            }

            // Add new participants
            foreach ($updateRequest->participants as $participant) {
                if (!$updateRequest->meetingRequest->hasFromParticipant($participant)) {
                    $updateRequest->meetingRequest->addFromParticipant($participant);
                }
            }
        } elseif ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getToSheet()) {
            // Remove removed participants
            foreach ($updateRequest->meetingRequest->getToParticipants() as $participant) {
                if (!in_array($participant, $updateRequest->participants)) {
                    $updateRequest->meetingRequest->removeToParticipant($participant);
                }
            }

            // Add new participants
            foreach ($updateRequest->participants as $participant) {
                if (!$updateRequest->meetingRequest->hasToParticipant($participant)) {
                    $updateRequest->meetingRequest->addToParticipant($participant);
                }
            }
        }
    }
}
