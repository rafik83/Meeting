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
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUpdateDataException;
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
     *
     * @throws IsNotAllowedToUpdateDataException
     */
    public function handle(EditRequest $editRequest)
    {
        if ($editRequest->meetingRequest->getFromSheet()->hasUser($editRequest->editor)) {

            // From edit
            $this->updateFromParticipants($editRequest);
            $this->requestRepository->set($editRequest->meetingRequest);

            // Add message
            if ($editRequest->description) {
                $this->messageRepository->add(new Message(
                  $editRequest->meetingRequest,
                  $editRequest->meetingRequest->getFromSheet(),
                  $editRequest->description,
                  $editRequest->date
                ));
            }

        } elseif ($editRequest->meetingRequest->getToSheet()->hasUser($editRequest->editor)) {

            // To edit
            $this->updateToParticipants($editRequest);
            $this->requestRepository->set($editRequest->meetingRequest);

            // Add message
            if ($editRequest->description) {
                $this->messageRepository->add(new Message(
                  $editRequest->meetingRequest,
                  $editRequest->meetingRequest->getToSheet(),
                  $editRequest->description,
                  $editRequest->date
                ));
            }

        } else {
            throw new IsNotAllowedToUpdateDataException('You are not allowed to update this meeting request.');
        }

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
            if (!in_array($participant, $editRequest->participants)) {
                $editRequest->meetingRequest->removeFromParticipant($participant);
                $this->events[] = [
                    'meeting_request.participant.removed',
                    new ParticipantRemovedEvent(
                        $editRequest->editor,
                        $participant,
                        $editRequest->meetingRequest,
                        $editRequest->description,
                        $editRequest->date
                    )
                ];
            }
        }

        // Add new participants
        foreach ($editRequest->participants as $participant) {
            if (!$editRequest->meetingRequest->hasFromParticipant($participant)) {
                $editRequest->meetingRequest->addFromParticipant($participant);
                $this->events[] = [
                    'meeting_request.participant.added',
                    new ParticipantAddedEvent(
                        $editRequest->editor,
                        $participant,
                        $editRequest->meetingRequest,
                        $editRequest->description,
                        $editRequest->date
                    )
                ];
            }
        }
    }

    /**
     * @param EditRequest $editRequest
     */
    private function updateToParticipants(EditRequest $editRequest)
    {
        // Remove removed participants
        foreach ($editRequest->meetingRequest->getToParticipants() as $participant) {
            if (!in_array($participant, $editRequest->participants)) {
                $editRequest->meetingRequest->removeToParticipant($participant);
                $this->events[] = [
                    'meeting_request.participant.removed',
                    new ParticipantRemovedEvent(
                        $editRequest->editor,
                        $participant,
                        $editRequest->meetingRequest,
                        $editRequest->description,
                        $editRequest->date
                    )
                ];
            }
        }

        // Add new participants
        foreach ($editRequest->participants as $participant) {
            if (!$editRequest->meetingRequest->hasToParticipant($participant)) {
                $editRequest->meetingRequest->addToParticipant($participant);
                $this->events[] = [
                    'meeting_request.participant.added',
                    new ParticipantAddedEvent(
                        $editRequest->editor,
                        $participant,
                        $editRequest->meetingRequest,
                        $editRequest->description,
                        $editRequest->date
                    )
                ];
            }
        }
    }
}
