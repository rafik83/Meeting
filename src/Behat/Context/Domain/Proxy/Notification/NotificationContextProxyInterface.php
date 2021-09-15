<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Notification;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Notification\NotificationManager;

interface NotificationContextProxyInterface
{
    public function getStorage(): StorageInterface;
    public function getNotificationManager(): NotificationManager;
}
