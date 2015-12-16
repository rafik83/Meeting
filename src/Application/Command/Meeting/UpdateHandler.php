<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepositoryInterface;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepositoryInterface
     */
    public function __construct(MeetingRepositoryInterface $meetingRepositoryInterface, NotificationRepositoryInterface $notificationRepository)
    {
        $this->meetingRepositoryInterface = $meetingRepositoryInterface;
        $this->notificationRepository     = $notificationRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        // Update participant
        if ($update->meeting->getFromSheet() === $update->sheet) {
            $notifications = $this->updateFromParticipants($update);
        } elseif ($update->meeting->getToSheet() === $update->sheet) {
            $notifications = $this->updateToParticipants($update);
        } else {
            throw new \RuntimeException('This sheet do not participate to this meeting.');
        }

        // Save meeeting
        $this->meetingRepositoryInterface->set($update->meeting);

        // Send notifications
        $this->notify($notifications);
    }

    /**
     * @param Update $update
     *
     * @return Notification[]
     */
    private function updateFromParticipants(Update $update)
    {
        $notifications = [];

        // Remove removed participants
        foreach ($update->meeting->getFromParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeFromParticipant($participant);
                $notifications[] = new Notification($update->user, $participant->getUser(), $update->date, 'participant.removed', '');
            }
        }

        // Add new participants
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addFromParticipant($participant);
                $notifications[] = new Notification($update->user, $participant->getUser(), $update->date, 'participant.add', '');
            }
        }

        return $notifications;
    }

    /**
     * @param Update $update
     *
     * @return Notification[]
     */
    private function updateToParticipants(Update $update)
    {
        $notifications = [];

        // Remove removed participants
        foreach ($update->meeting->getToParticipants() as $participant) {
            if (!in_array($participant, $update->participants)) {
                $update->meeting->removeToParticipant($participant);
                $notifications[] = new Notification($update->user, $participant->getUser(), $update->date, 'participant.removed', '');
            }
        }

        // Add new participants
        foreach ($update->participants as $participant) {
            if (!$update->meeting->hasFromParticipant($participant)) {
                $update->meeting->addToParticipant($participant);
                $notifications[] = new Notification($update->user, $participant->getUser(), $update->date, 'participant.add', '');
            }
        }

        return $notifications;
    }

    /**
     * @param Notification[] $notifications
     */
    private function notify(array $notifications)
    {
        foreach ($notifications as $notification) {
            $this->notificationRepository->add($notification);
        }
    }
}
