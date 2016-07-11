<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Notification;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class NotificationViewFactory
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * NotificationViewFactory constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param RequestRepositoryInterface      $requestRepository
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param TranslatorInterface             $translator
     * @param RouterInterface                 $router
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        RequestRepositoryInterface $requestRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->requestRepository      = $requestRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->translator             = $translator;
        $this->router                 = $router;
    }

    /**
     * Count unread notification for this event and user
     *
     * @param Event $event
     * @param User  $user
     *
     * @return int
     */
    public function countUnreadNotificationByEventAndUser(Event $event, User $user)
    {
        $notifications = $this->getNotificationsByEventAndUser($event, $user, $user->getLocale());

        return array_reduce($notifications, function ($carry, NotificationView $notificationView) {
            return $notificationView->read ? $carry : ++$carry;
        }, 0);
    }

    /**
     * Get unread notification for this event and user
     *
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     *
     * @return NotificationView[]
     */
    public function getNotificationsByEventAndUser(Event $event, User $user, $locale)
    {
        $notifications   = $this->notificationRepository->getNotificationsByEventAndUser($event, $user);
        $receivedRequest = $this->requestRepository->getRequestsByEventAndUser($event, $user);

        // Compute notification view
        $notifications = array_map(function (Notification $notification) {
            return new NotificationView(
                $notification->getId(),
                $notification->getAction(),
                $notification->getMessage(),
                $notification->getCreatedAt(),
                $notification->isRead(),
                $notification->getUrl()
            );
        }, $notifications);

        // Add notification views for request, force read to false is request is not accepted, refused or canceled
        $notifications = array_merge($notifications, array_map(function (Request $request) use ($user, $locale) {
            $message = $this->translator->trans(
                'notification.meeting_request.receive.message',
                ['%from_sheet%' => $this->sheetInfoGuesser->guessSheetName($request->getFromSheet(), $locale)],
                'notifications',
                $user->getLocale()
            );

            return new NotificationView(
                null,
                'meeting_request.receive',
                $message,
                $request->getCreatedAt(),
                !$request->isSent(),
                $this->router->generateMeetingRequest($request->getToSheet(), $request)
            );
        }, $receivedRequest));

        usort($notifications, function (NotificationView $one, NotificationView $another) {
            return $another->date->getTimestamp() - $one->date->getTimestamp();
        });

        return $notifications;
    }
}
