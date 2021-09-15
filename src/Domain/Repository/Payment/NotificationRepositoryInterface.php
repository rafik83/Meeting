<?php

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Proximum\Vimeet\Domain\Model\Payment\Notification;

interface NotificationRepositoryInterface
{
    /**
     * @param Notification $notification
     */
    public function add(Notification $notification);
}
