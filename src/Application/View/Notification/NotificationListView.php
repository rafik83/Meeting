<?php

namespace Proximum\Vimeet\Application\View\Notification;

class NotificationListView
{
    /**
     * @var NotificationView[]
     */
    private $notificationViews;

    /**
     * NotificationListView constructor.
     *
     * @param NotificationView[] $notificationViews
     */
    public function __construct(array $notificationViews)
    {
        $this->notificationViews = $notificationViews;
    }

    /**
     * @return NotificationView[]
     */
    public function getNotificationViews()
    {
        return $this->notificationViews;
    }
}
