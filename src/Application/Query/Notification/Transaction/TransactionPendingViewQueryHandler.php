<?php

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\AbstractNotificationQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;

class TransactionPendingViewQueryHandler extends AbstractNotificationQueryHandler
{
    public function handle(TransactionPendingViewQuery $query): NotificationView
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
