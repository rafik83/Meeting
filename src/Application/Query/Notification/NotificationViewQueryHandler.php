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
     * @param SheetNotificationViewQueryHandler   $sheetNotificationViewQueryHandler
     * @param PackageNotificationViewQueryHandler $packageNotificationViewQueryHandler
     */
    public function __construct(
        SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler,
        PackageNotificationViewQueryHandler $packageNotificationViewQueryHandler
    ) {
        $this->sheetNotificationViewQueryHandler   = $sheetNotificationViewQueryHandler;
        $this->packageNotificationViewQueryHandler = $packageNotificationViewQueryHandler;
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

        $packageNotificationView = $this->packageNotificationViewQueryHandler->handle(
            new PackageNotificationViewQuery($query->sheet)
        );

        $notificationViews = array_merge($sheetNotificationViews, $packageNotificationView);

        return new NotificationListView($notificationViews);
    }
}
