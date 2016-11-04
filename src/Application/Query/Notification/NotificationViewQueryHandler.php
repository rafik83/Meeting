<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQueryHandler;

class NotificationViewQueryHandler
{
    /**
     * @var SheetNotificationViewQueryHandler
     */
    private $sheetNotificationViewQueryHandler;

    /**
     * NotificationViewQueryHandler constructor.
     *
     * @param SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler
     */
    public function __construct(SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler)
    {
        $this->sheetNotificationViewQueryHandler = $sheetNotificationViewQueryHandler;
    }

    /**
     * @param NotificationViewQuery $query
     */
    public function handle(NotificationViewQuery $query)
    {
        $notificationViews = $this->sheetNotificationViewQueryHandler->handle(
            new SheetNotificationViewQuery($query->sheet)
        );
    }
}
