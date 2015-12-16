<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepositoryInterface;

    /**
     * UpdateHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepositoryInterface
     */
    public function __construct(MeetingRepositoryInterface $meetingRepositoryInterface)
    {
        $this->meetingRepositoryInterface = $meetingRepositoryInterface;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        // Update participant
        if ($update->meeting->getFromSheet() === $update->sheet) {
            $notifications = $this->updateFromParticipant($update);
        } elseif ($update->meeting->getToSheet() === $update->sheet) {
            $notifications = $this->updateToParticipant($update);
        } else {
            throw new \RuntimeException('This sheet do not participate to this meeting.');
        }

        // Save meeeting
        $this->meetingRepositoryInterface->set($update->meeting);

        // Send notifications
        $this->notify($notifications);
    }

    private function updateFromParticipant(Update $update)
    {
        $notifications = [];

        // Remove removed participants
        foreach ($update->meeting->getFromParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeFromParticipant($participant);
                $notifications[] = $this->createRemovedParticipantNotification($participant);
            }
        }

        // Add new participant
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addFromParticipant($participant);
                $notifications = $this->createAddedParticipantNotification($participant);
            }
        }

        return $notifications;
    }

    private function updateToParticipant(Update $update)
    {
        $notifications = [];

        // Remove removed participants
        foreach ($update->meeting->getToParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeToParticipant($participant);
                $notifications[] = $this->createRemovedParticipantNotification($participant);
            }
        }

        // Add new participant
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addToParticipant($participant);
                $notifications = $this->createAddedParticipantNotification($participant);
            }
        }

        return $notifications;
    }

    private function createRemovedParticipantNotification(Participant $participant)
    {
        return [];
    }

    private function createAddedParticipantNotification(Participant $participant)
    {
        return [];
    }

    private function notify(array $notifications)
    {
        foreach ($notifications as $notification) {

        }
    }
}
