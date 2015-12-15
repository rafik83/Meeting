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
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @param RefuseRequest $refuseRequest
     */
    public function handle(RefuseRequest $refuseRequest)
    {
        $refuseRequest->request->setState(Request::STATE_REFUSED);

        if (!$refuseRequest->request->hasFromParticipants()) {
            $notification = new Notification(
                $refuseRequest->request->getFromSheet()->getEvent(),
                $refuseRequest->emitter,
                $refuseRequest->request->getCreator(),
                $this->createdAt,
                'meeting_request.refuse'
            );

            $message = $this->translator->trans(
                'notification.meeting_request.refuse.' . ($refuseRequest->message ? 'withMessage' : 'withoutMessage'),
                [
                    '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($refuseRequest->request->getToSheet()),
                    '%message%'   => $refuseRequest->message
                ],
                null,
                $refuseRequest->request->getCreator()->getLocale()
            );

            $notification->setMessage($message);

            $this->notificationRepository->add($notification);
            $refuseRequest->request->addNotifications($notification);
        } else {
            foreach ($refuseRequest->request->getFromParticipants() as $participant) {
                $notification = new Notification(
                    $refuseRequest->request->getFromSheet()->getEvent(),
                    $refuseRequest->emitter,
                    $participant->getUser(),
                    $this->createdAt,
                    'meeting_request.refuse'
                );

                $message = $this->translator->trans(
                    'notification.meeting_request.refuse.' . ($refuseRequest->message ? 'withMessage' : 'withoutMessage'),
                    [
                        '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($refuseRequest->request->getToSheet()),
                        '%message%'   => $refuseRequest->message
                    ],
                    null,
                    $refuseRequest->request->getCreator()->getLocale()
                );

                $notification->setMessage($message);

                $this->notificationRepository->add($notification);
                $refuseRequest->request->addNotifications($notification);
            }
        }

        $this->requestRepository->set($refuseRequest->request);
    }
}
