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
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionNotificationViewQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;

class NotificationViewQueryHandler
{
    /**
     * @var SheetNotificationViewQueryHandler
     */
    private $sheetNotificationViewQueryHandler;
    /**
     * @var TransactionNotificationViewQueryHandler
     */
    private $transactionNotificationViewQueryHandler;

    /**
     * NotificationViewQueryHandler constructor.
     *
     * @param SheetNotificationViewQueryHandler       $sheetNotificationViewQueryHandler
     * @param TransactionNotificationViewQueryHandler $transactionNotificationViewQueryHandler
     */
    public function __construct(
        SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler,
        TransactionNotificationViewQueryHandler $transactionNotificationViewQueryHandler
    ) {
        $this->sheetNotificationViewQueryHandler       = $sheetNotificationViewQueryHandler;
        $this->transactionNotificationViewQueryHandler = $transactionNotificationViewQueryHandler;
    }

    /**
     * @param NotificationViewQuery $query
     *
     * @return NotificationListView
     */
    public function handle(NotificationViewQuery $query)
    {
        $notificationViews = $this->sheetNotificationViewQueryHandler->handle(
            new SheetNotificationViewQuery($query->sheet)
        );

        return new NotificationListView($notificationViews);
    }
}
