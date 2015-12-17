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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class CancelHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * CancelHandler constructor.
     *
     * @param MeetingRepositoryInterface      $meetingRepository
     * @param NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository, NotificationRepositoryInterface $notificationRepository)
    {
        $this->meetingRepository      = $meetingRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @param Cancel $cancel
     */
    public function handle(Cancel $cancel)
    {
        $cancel->meeting->cancel();

        $this->meetingRepository->set($cancel->meeting);

        foreach ($cancel->meeting->getFromParticipants() as $participant) {
            $this->notifyParticipant($cancel, $participant);
        }

        foreach ($cancel->meeting->getToParticipants() as $participant) {
            $this->notifyParticipant($cancel, $participant);
        }
    }

    /**
     * @param Cancel      $cancel
     * @param Participant $participant
     */
    private function notifyParticipant(Cancel $cancel, Participant $participant)
    {
        $notification = new Notification($cancel->user, $participant->getUser(), $cancel->date, 'metting.cancel', $cancel->message);

        $this->notificationRepository->add($notification);
    }
}
