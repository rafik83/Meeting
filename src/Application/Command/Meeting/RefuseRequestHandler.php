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
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class RefuseRequestHandler
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
     * @param RefuseRequest $refuseRequest
     */
    public function handle(RefuseRequest $refuseRequest)
    {
        $refuseRequest->request->setState(Request::STATE_REFUSED);

        if (!$refuseRequest->request->hasFromParticipants()) {
            $notification = new Notification(
                $refuseRequest->request->getFrom()->getEvent(),
                $refuseRequest->emitter,
                $refuseRequest->request->getCreator(),
                $this->createdAt,
                'meeting_request.refuse'
            );
            $notification->setMessage($refuseRequest->message);

            $this->notificationRepository->add($notification);
            $refuseRequest->request->addNotifications($notification);
        } else {
            foreach ($refuseRequest->request->getFromParticipants() as $participant) {
                $notification = new Notification(
                    $refuseRequest->request->getFrom()->getEvent(),
                    $refuseRequest->emitter,
                    $participant->getUser(),
                    $this->createdAt,
                    'meeting_request.refuse'
                );
                $notification->setMessage($refuseRequest->message);

                $this->notificationRepository->add($notification);
                $refuseRequest->request->addNotifications($notification);
            }
        }

        $this->requestRepository->set($refuseRequest->request);
    }
}
