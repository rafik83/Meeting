<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param UpdateRequestTo $updateRequestTo
     */
    public function handle(UpdateRequestTo $updateRequestTo)
    {
        if (!$updateRequestTo->meetingRequest->getToSheet()->hasUser($updateRequestTo->editor)) {
            throw new \RuntimeException('You are not allowed to update this request.');
        }

        // Event queue
        $events = [];

        // Remove removed participants
        foreach ($updateRequestTo->meetingRequest->getToParticipants() as $participant) {
            if (!in_array($participant, $updateRequestTo->participants)) {
                $updateRequestTo->meetingRequest->removeToParticipant($participant);
                $events[] = $this->createRemovedEvent($updateRequestTo, $participant);
            }
        }

        // Add new participants
        foreach ($updateRequestTo->participants as $participant) {
            if (!$updateRequestTo->meetingRequest->hasToParticipant($participant)) {
                $updateRequestTo->meetingRequest->addToParticipant($participant);
                $events[] = $this->createAddedEvent($updateRequestTo, $participant);
            }
        }

        // Save request
        $this->requestRepository->set($updateRequestTo->meetingRequest);

        // Add message
        if ($updateRequestTo->description) {
            $this->messageRepository->add(new Message(
              $updateRequestTo->meetingRequest,
              $updateRequestTo->meetingRequest->getToSheet(),
              $updateRequestTo->description,
              $updateRequestTo->date
            ));
        }

        // Dispatch events
        while ($event = array_shift($events)) {
            $this->eventDispatcher->dispatch($event[0], $event[1]);
        }
    }

    /**
     * @param UpdateRequestTo $updateRequestTo
     * @param Participant       $participant
     *
     * @return array
     */
    private function createAddedEvent(UpdateRequestTo $updateRequestTo, Participant $participant)
    {
        return [
            'meeting_request.participant.added',
            new ParticipantAddedEvent(
                $updateRequestTo->editor,
                $participant,
                $updateRequestTo->meetingRequest,
                $updateRequestTo->description,
                $updateRequestTo->date
            )
        ];
    }

    /**
     * @param UpdateRequestTo $updateRequestTo
     * @param Participant       $participant
     *
     * @return array
     */
    private function createRemovedEvent(UpdateRequestTo $updateRequestTo, Participant $participant)
    {
        return [
            'meeting_request.participant.removed',
            new ParticipantRemovedEvent(
                $updateRequestTo->editor,
                $participant,
                $updateRequestTo->meetingRequest,
                $updateRequestTo->description,
                $updateRequestTo->date
            )
        ];
    }
}
