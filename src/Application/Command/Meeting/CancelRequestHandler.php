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
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\User;
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
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param RequestRepositoryInterface      $requestRepository
     * @param NotificationRepositoryInterface $notificationRepository
     * @param DateTimeInterface               $createdAt
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param TranslatorInterface             $translator
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        NotificationRepositoryInterface $notificationRepository,
        DateTimeInterface $createdAt,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator
    ) {
        $this->requestRepository      = $requestRepository;
        $this->notificationRepository = $notificationRepository;
        $this->createdAt              = $createdAt;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->translator             = $translator;
    }

    /**
     * @param CancelRequest $cancelRequest
     */
    public function handle(CancelRequest $cancelRequest)
    {
        $cancelRequest->request->setState(Request::STATE_CANCEL);
        $this->requestRepository->set($cancelRequest->request);

        $this->notify($cancelRequest);
    }

    /**
     * @param CancelRequest $cancelRequest
     */
    private function notify(CancelRequest $cancelRequest)
    {
        $notifications = [];

        if (!$cancelRequest->request->hasToParticipants()) {
            foreach ($cancelRequest->request->getToSheet()->getParticipants() as $participant) {
                if ($participant->isOwner()) {
                    $notifications[] = $this->notifyUser($cancelRequest, $participant->getUser());
                }
            }
        } else {
            foreach ($cancelRequest->request->getToParticipants() as $participant) {
                $notifications[] = $this->notifyUser($cancelRequest, $participant->getUser());
            }
        }

        foreach ($notifications as $notification) {
            $this->notificationRepository->add($notification);
            $cancelRequest->request->addNotifications($notification);
        }

        $this->requestRepository->set($cancelRequest->request);
    }

    /**
     * @param CancelRequest $cancelRequest
     * @param User          $user
     *
     * @return Notification
     */
    private function notifyUser(CancelRequest $cancelRequest, User $user)
    {
        $message = $this->translator->trans(
            'notification.meeting_request.cancel.' . ($cancelRequest->message ? 'withMessage' : 'withoutMessage'),
            [
                '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($cancelRequest->request->getFromSheet()),
                '%message%'   => $cancelRequest->message
            ],
            null,
            $user->getLocale()
        );

        $notification = new Notification(
            $cancelRequest->request->getFromSheet()->getEvent(),
            $cancelRequest->emitter,
            $user,
            $this->createdAt,
            'meeting_request.cancel',
            $message
        );

        return $notification;
    }
}
