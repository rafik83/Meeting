<?php

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Query\Notification\Package\PackageNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Package\PackageNotificationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Sheet\SheetNotificationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionNotificationViewQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;
use Proximum\Vimeet\Application\View\Notification\NotificationView;

class NotificationViewQueryHandler
{
    /** @var SheetNotificationViewQueryHandler */
    private $sheetNotificationViewQueryHandler;

    /** @var TransactionNotificationViewQueryHandler */
    private $transactionNotificationViewQueryHandler;

    /** @var PackageNotificationViewQueryHandler */
    private $packageNotificationViewQueryHandler;

    /** @var NotificationView[] */
    private $notificationViews;

    public function __construct(
        SheetNotificationViewQueryHandler $sheetNotificationViewQueryHandler,
        TransactionNotificationViewQueryHandler $transactionNotificationViewQueryHandler,
        PackageNotificationViewQueryHandler $packageNotificationViewQueryHandler
    ) {
        $this->sheetNotificationViewQueryHandler       = $sheetNotificationViewQueryHandler;
        $this->transactionNotificationViewQueryHandler = $transactionNotificationViewQueryHandler;
        $this->packageNotificationViewQueryHandler     = $packageNotificationViewQueryHandler;
        $this->notificationViews                       = [];
    }

    public function handle(NotificationViewQuery $query): NotificationListView
    {
        $sheetNotificationViews = $this->sheetNotificationViewQueryHandler->handle(
            new SheetNotificationViewQuery($query->sheet)
        );

        $this->addNotifications($sheetNotificationViews);

        if (null !== $query->sheet->getPackage() && $query->sheet->getPackage()->isPassable()) {
            $packageNotificationView = $this->packageNotificationViewQueryHandler->handle(
                new PackageNotificationViewQuery($query->sheet)
            );

            $transactionNotificationView = $this->transactionNotificationViewQueryHandler->handle(
                new TransactionNotificationViewQuery($query->sheet)
            );

            $this->addNotifications($packageNotificationView);
            $this->addNotifications($transactionNotificationView);
        }

        return new NotificationListView($this->notificationViews);
    }

    /**
     * @param NotificationView[] $notificationViews
     */
    private function addNotifications(array $notificationViews): void
    {
        $this->notificationViews = array_merge($this->notificationViews, $notificationViews);
    }
}
