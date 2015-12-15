<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class CancelRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param NotificationRepositoryInterface $notificationRepository
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        NotificationRepositoryInterface $notificationRepository,
        DateTimeInterface $createdAt
    ) {
        $this->requestRepository      = $requestRepository;
        $this->notificationRepository = $notificationRepository;
        $this->createdAt              = $createdAt;
    }

    /**
     * @param CancelRequest $cancelRequest
     */
    public function handle(CancelRequest $cancelRequest)
    {
        $cancelRequest->request->setState(Request::STATE_CANCEL);

        if (!$cancelRequest->request->hasToParticipants()) {
            foreach ($cancelRequest->request->getTo()->getParticipants() as $participant) {
                if ($participant->isOwner()) {
                    $this->notify($cancelRequest, $participant);
                }
            }
        } else {
            foreach ($cancelRequest->request->getToParticipants() as $participant) {
                $this->notify($cancelRequest, $participant);
            }
        }

        $this->requestRepository->set($cancelRequest->request);
    }

    /**
     * @param CancelRequest $cancelRequest
     * @param Participant   $participant
     */
    private function notify(CancelRequest $cancelRequest, Participant $participant)
    {
        $notification = new Notification(
            $cancelRequest->emitter,
            $participant->getUser(),
            $this->createdAt,
            'meeting_request.cancel'
        );

        $notification->setMessage($cancelRequest->message);
        $this->notificationRepository->add($notification);
        $cancelRequest->request->addNotifications($notification);
    }
}
