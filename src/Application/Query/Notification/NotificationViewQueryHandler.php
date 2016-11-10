<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Query\Notification\Package\PackageNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Package\PackageNotificationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;
use Proximum\Vimeet\Application\View\Notification\NotificationView;

class NotificationViewQueryHandler
{
    /**
     * @var SheetNotificationViewQueryHandler
     */
    private $sheetNotificationViewQueryHandler;

    /**
     * @var PackageNotificationViewQueryHandler
     */
    private $packageNotificationViewQueryHandler;

    /**
     * @var NotificationView[]
     */
    private $notificationViews;

    /**
     * NotificationViewQueryHandler constructor.
     *
     * @param SheetNotificationViewQueryHandler   $sheetNotificationViewQueryHandler
     * @param PackageNotificationViewQueryHandler $packageNotificationViewQueryHandler
     */
    public function __construct(
        SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler,
        PackageNotificationViewQueryHandler $packageNotificationViewQueryHandler
    ) {
        $this->sheetNotificationViewQueryHandler   = $sheetNotificationViewQueryHandler;
        $this->packageNotificationViewQueryHandler = $packageNotificationViewQueryHandler;
        $this->notificationViews                   = [];
    }

    /**
     * @param NotificationViewQuery $query
     *
     * @return NotificationListView
     */
    public function handle(NotificationViewQuery $query)
    {
        $sheetNotificationViews = $this->sheetNotificationViewQueryHandler->handle(
            new SheetNotificationViewQuery($query->sheet)
        );

        $this->addNotifications($sheetNotificationViews);

        if ($query->sheet->getPackage() !== null && $query->sheet->getPackage()->isPassable()) {
            $packageNotificationView = $this->packageNotificationViewQueryHandler->handle(
                new PackageNotificationViewQuery($query->sheet)
            );

            $this->addNotifications($packageNotificationView);
        }

        return new NotificationListView($this->notificationViews);
    }

    /**
     * @param NotificationView[] $notificationViews
     */
    private function addNotifications(array $notificationViews)
    {
        array_merge($this->notificationViews, $notificationViews);
    }
}
