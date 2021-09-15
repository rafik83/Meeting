<?php

namespace Proximum\Vimeet\Behat\Proxy\Notification;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Notification\NotificationContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Notification\NotificationManager;

class NotificationContextProxy implements NotificationContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var NotificationManager */
    private $notificationManager;

    public function __construct(StorageInterface $storage, NotificationManager $notificationManager)
    {
        $this->storage = $storage;
        $this->notificationManager = $notificationManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getNotificationManager(): NotificationManager
    {
        return $this->notificationManager;
    }
}
