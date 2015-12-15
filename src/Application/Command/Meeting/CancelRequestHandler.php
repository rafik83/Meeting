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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
            $cancelRequest->request->getFrom()->getEvent(),
            $cancelRequest->emitter,
            $participant->getUser(),
            $this->createdAt,
            'meeting_request.cancel'
        );

        $message = $this->translator->trans(
            'notification.meeting_request.cancel.' . ($cancelRequest->message ? 'withMessage' : 'withoutMessage'),
            [
                '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($cancelRequest->request->getFrom()),
                '%message%'   => $cancelRequest->message
            ],
            null,
            $participant->getUser()->getLocale()
        );

        $notification->setMessage($message);
        $this->notificationRepository->add($notification);
        $cancelRequest->request->addNotifications($notification);
    }
}
