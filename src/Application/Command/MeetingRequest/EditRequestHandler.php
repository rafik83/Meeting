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
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EditRequestHandler
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
     * @var array
     */
    private $events = [];

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
    ){
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param EditRequest $editRequest
     */
    public function handle(EditRequest $editRequest)
    {
        // Update participant
        $this->updateFromParticipants($editRequest);

        $this->requestRepository->set($editRequest->meetingRequest);

        // Add message
        $this->messageRepository->add(new Message(
          $editRequest->meetingRequest,
          $editRequest->meetingRequest->getFromSheet(),
          $editRequest->description,
          $editRequest->date
        ));

        // Dispatch events
        while ($event = array_shift($this->events)) {
            $this->eventDispatcher->dispatch($event[0], $event[1]);
        }
    }

    /**
     * @param EditRequest $editRequest
     */
    private function updateFromParticipants(EditRequest $editRequest)
    {
        // Remove removed participants
        foreach ($editRequest->meetingRequest->getFromParticipants() as $participant) {
            if (!in_array($participant, $editRequest->fromParticipants)) {
                $editRequest->meetingRequest->removeFromParticipant($participant);
                $this->events[] = ['meeting_request.participant.removed', new ParticipantRemovedEvent($editRequest->editor, $participant, $editRequest->meetingRequest, $editRequest->description, $editRequest->date)];
            }
        }

        // Add new participants
        foreach ($editRequest->fromParticipants as $participant) {
            if (!$editRequest->meetingRequest->hasFromParticipant($participant)) {
                $editRequest->meetingRequest->addFromParticipant($participant);
                $this->events[] = ['meeting_request.participant.added', new ParticipantAddedEvent($editRequest->editor, $participant, $editRequest->meetingRequest, $editRequest->description, $editRequest->date)];
            }
        }
    }
}
