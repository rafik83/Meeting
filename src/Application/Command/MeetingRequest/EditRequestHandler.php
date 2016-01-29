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
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @param Sheet       $sheet
     */
    public function handle(EditRequest $editRequest, Sheet $sheet)
    {
        if($sheet === $editRequest->meetingRequest->getFromSheet()) {
            if($editRequest->meetingRequest->getState() != Request::STATE_SENT && $editRequest->meetingRequest->getState() != Request::STATE_APPROVED) {
                throw new AccessDeniedException('You can not update this data');
            }
            else {
                // Update participant
                if ($editRequest->meetingRequest->getFromSheet() === $sheet) {
                    $this->updateFromParticipants($editRequest);
                } elseif ($editRequest->meetingRequest->getToSheet() === $sheet) {
                    $this->updateToParticipants($editRequest);
                } else {
                    throw new \RuntimeException('This sheet do not participate to this meeting.');
                }

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
        }
        elseif($sheet === $editRequest->meetingRequest->getToSheet()) {
            if($editRequest->meetingRequest->getState() != Request::STATE_APPROVED) {
                throw new AccessDeniedException('You can not update this data');
            }
            else {
                // Update participant
                if ($editRequest->meetingRequest->getFromSheet() === $sheet) {
                    $this->updateFromParticipants($editRequest);
                } elseif ($editRequest->meetingRequest->getToSheet() === $sheet) {
                    $this->updateToParticipants($editRequest);
                } else {
                    throw new \RuntimeException('This sheet do not participate to this meeting.');
                }

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
        }
        else
        {
            // Update participant
            if ($editRequest->meetingRequest->getFromSheet() === $sheet) {
                $this->updateFromParticipants($editRequest);
            } elseif ($editRequest->meetingRequest->getToSheet() === $sheet) {
                $this->updateToParticipants($editRequest);
            } else {
                throw new \RuntimeException('This sheet do not participate to this meeting.');
            }

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
        foreach ($editRequest->fromParticipants as $participant) {
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
        // Remove removed participants;
        foreach ($editRequest->meetingRequest->getToParticipants() as $participant) {
            if (!in_array($participant, $editRequest->fromParticipants)) {
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
        foreach ($editRequest->fromParticipants as $participant) {
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
