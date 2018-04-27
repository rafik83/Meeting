<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\AbstractNotificationQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;

class TransactionPendingViewQueryHandler extends AbstractNotificationQueryHandler
{
    /**
     * @param TransactionPendingViewQuery $query
     *
     * @return NotificationView
     */
    public function handle(TransactionPendingViewQuery $query)
    {
        return new NotificationView(
            $query->transaction->getDate(),
            Category::BILLING_ICON,
            Notification::CATEGORY_TRANSACTION,
            'notification.transaction.pending',
            $this->router->generate('event_order_list', ['sheet' => $query->transaction->getSheet()->getId()]),
            Notification::PRIORITY_IMPORTANT,
            [
                '%amount%'   => $query->transaction->getAmount(),
                '%currency%' => $query->transaction->getCurrency(),
            ]
        );
    }
}
