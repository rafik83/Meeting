<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Package;

use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class PackageNotificationViewQueryHandler
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var NoOrderNotificationViewQueryHandler
     */
    private $noOrderNotificationViewQueryHandler;

    /**
     * PackageNotificationViewQuery constructor.
     *
     * @param NotificationRepositoryInterface     $notificationRepository
     * @param NoOrderNotificationViewQueryHandler $noOrderNotificationViewQueryHandler
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        NoOrderNotificationViewQueryHandler $noOrderNotificationViewQueryHandler
    ) {
        $this->notificationRepository              = $notificationRepository;
        $this->noOrderNotificationViewQueryHandler = $noOrderNotificationViewQueryHandler;
    }

    /**
     * @param PackageNotificationViewQuery $query
     *
     * @return NotificationView[]
     */
    public function handle(PackageNotificationViewQuery $query)
    {
        $packageNotification = $this->notificationRepository->findByType(
            $query->sheet,
            Notification::TYPE_PACKAGE_SELECTED
        );

        $notificationViews = [];

        if (null !== $packageNotification) {
            $notificationViews[] = $this->noOrderNotificationViewQueryHandler->handle(
                new NoOrderNotificationViewQuery($query->sheet)
            );
        }

        return $notificationViews;
    }
}
