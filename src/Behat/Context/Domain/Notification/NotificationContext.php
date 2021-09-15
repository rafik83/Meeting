<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Notification;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Notification\NotificationContextProxyInterface;

class NotificationContext implements Context
{
    /** @var NotificationContextProxyInterface */
    private $notificationContextProxy;

    public function __construct(NotificationContextProxyInterface $notificationContextProxy)
    {
        $this->notificationContextProxy = $notificationContextProxy;
    }

    /**
     * @Given /^there is a pending transaction notification for this sheet/
     */
    public function createPendingTransactionNotification(): void
    {
        $sheet = $this->notificationContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $notification = $this->notificationContextProxy
            ->getNotificationManager()
            ->createTransactionPendingNotification($sheet)
        ;
        $this->notificationContextProxy
            ->getStorage()
            ->set('notification', $notification)
        ;
    }

    /**
     * @Given /^there is a package selected notification for this sheet/
     */
    public function createPackageSelectedNotification(): void
    {
        $sheet = $this->notificationContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $notification = $this->notificationContextProxy
            ->getNotificationManager()
            ->createPackageSelectedNotification($sheet)
        ;
        $this->notificationContextProxy
            ->getStorage()
            ->set('notification', $notification)
        ;
    }

    /**
     * @Given /^there is a sheet translation completeness notification for this sheet/
     */
    public function createSheetTranslationCompletenessNotification(): void
    {
        $sheet = $this->notificationContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $notification = $this->notificationContextProxy
            ->getNotificationManager()
            ->createSheetTranslationCompletenessNotification($sheet)
        ;
        $this->notificationContextProxy
            ->getStorage()
            ->set('notification', $notification)
        ;
    }
}
