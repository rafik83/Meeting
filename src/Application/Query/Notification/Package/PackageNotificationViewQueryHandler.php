<?php

namespace Proximum\Vimeet\Application\Query\Notification\Package;

use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class PackageNotificationViewQueryHandler
{
    /** @var NotificationRepositoryInterface */
    private $notificationRepository;

    /** @var NoOrderNotificationViewQueryHandler */
    private $noOrderNotificationViewQueryHandler;

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
    public function handle(PackageNotificationViewQuery $query): array
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
