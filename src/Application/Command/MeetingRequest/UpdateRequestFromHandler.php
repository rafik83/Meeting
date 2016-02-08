<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Event\MeetingRequest\MessageEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     *
     * EditRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param UpdateRequestFrom $updateRequestFrom
     */
    public function handle(UpdateRequestFrom $updateRequestFrom)
    {
        if (!$updateRequestFrom->meetingRequest->getFromSheet()->hasUser($updateRequestFrom->editor)) {
            throw new \RuntimeException('You are not allowed to update this request.');
        }

        // Event queue
        $events = [];

        // Remove removed participants
        foreach ($updateRequestFrom->meetingRequest->getFromParticipants() as $participant) {
            if (!in_array($participant, $updateRequestFrom->participants)) {
                $updateRequestFrom->meetingRequest->removeFromParticipant($participant);
                $events[] = $this->createRemovedEvent($updateRequestFrom, $participant);
            }
        }

        // Add new participants
        foreach ($updateRequestFrom->participants as $participant) {
            if (!$updateRequestFrom->meetingRequest->hasFromParticipant($participant)) {
                $updateRequestFrom->meetingRequest->addFromParticipant($participant);
                $events[] = $this->createAddedEvent($updateRequestFrom, $participant);
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
                $updateRequestFrom->date
            );

            $this->messageRepository->add($message);

            $event[] = ['meeting_request.update.message', new MessageEvent($message, $updateRequestFrom->editor)];
        }

        // Dispatch events
        while ($event = array_shift($events)) {
            $this->eventDispatcher->dispatch($event[0], $event[1]);
        }
    }

    /**
     * @param UpdateRequestFrom $updateRequestFrom
     * @param Participant       $participant
     *
     * @return array
     */
    private function createAddedEvent(UpdateRequestFrom $updateRequestFrom, Participant $participant)
    {
        return [
            'meeting_request.participant.added',
            new ParticipantAddedEvent(
                $updateRequestFrom->editor,
                $participant,
                $updateRequestFrom->meetingRequest,
                $updateRequestFrom->description,
                $updateRequestFrom->date
            )
        ];
    }

    /**
     * @param UpdateRequestFrom $updateRequestFrom
     * @param Participant       $participant
     *
     * @return array
     */
    private function createRemovedEvent(UpdateRequestFrom $updateRequestFrom, Participant $participant)
    {
        return [
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent(
                $updateRequestFrom->editor,
                $participant,
                $updateRequestFrom->meetingRequest,
                $updateRequestFrom->description,
                $updateRequestFrom->date
            )
        ];
    }
}
