<?php

namespace Proximum\Vimeet\Application\Query\Notification\Package;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\AbstractNotificationQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;

class NoOrderNotificationViewQueryHandler extends AbstractNotificationQueryHandler
{
    /**
     * @param NoOrderNotificationViewQuery $query
     *
     * @return NotificationView
     */
    public function handle(NoOrderNotificationViewQuery $query)
    {
        return new NotificationView(
            $query->sheet->getCreatedAt(),
            Category::SHEET_ICON,
            Notification::CATEGORY_PACKAGE,
            'notification.package.noOrder',
            $this->router->generate('event_package_step', ['step' => 1, 'sheet' => $query->sheet->getId()]),
            Notification::PRIORITY_REQUIRED
        );
    }
}
