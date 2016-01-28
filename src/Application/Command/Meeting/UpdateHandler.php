<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Event\Meeting\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

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
     * UpdateHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        // Update participant
        if ($update->meeting->getFromSheet() === $update->sheet) {
            $this->updateFromParticipants($update);
        } elseif ($update->meeting->getToSheet() === $update->sheet) {
            $this->updateToParticipants($update);
        } else {
            throw new \RuntimeException('This sheet do not participate to this meeting.');
        }

        // Save meeeting
        $this->meetingRepository->set($update->meeting);

        // Add message
        $this->messageRepository->add(new Message(
            $update->meeting,
            $update->sheet,
            $update->message,
            $update->date
        ));

        // Dispatch events
        while ($event = array_shift($this->events)) {
            $this->eventDispatcher->dispatch($event[0], $event[1]);
        }
    }

    /**
     * @param Update $update
     */
    private function updateFromParticipants(Update $update)
    {
        // Remove removed participants
        foreach ($update->meeting->getFromParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeFromParticipant($participant);
                $this->events[] = ['participant.removed', new ParticipantRemovedEvent($update->user, $participant, $update->meeting, $update->message, $update->date)];
            }
        }

        // Add new participants
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addFromParticipant($participant);
                $this->events[] = ['participant.added', new ParticipantAddedEvent($update->user, $participant, $update->meeting, $update->message, $update->date)];
            }
        }
    }

    /**
     * @param Update $update
     */
    private function updateToParticipants(Update $update)
    {
        // Remove removed participants
        foreach ($update->meeting->getToParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeToParticipant($participant);
                $this->events[] = ['participant.added', new ParticipantRemovedEvent($update->user, $participant, $update->meeting, $update->message, $update->date)];
            }
        }

        // Add new participants
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addToParticipant($participant);
                $this->events[] = ['participant.removed', new ParticipantAddedEvent($update->user, $participant, $update->meeting, $update->message, $update->date)];
            }
        }
    }
}
