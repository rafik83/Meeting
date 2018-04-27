<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Proximum\Vimeet\Domain\Model\Payment\Notification;

interface NotificationRepositoryInterface
{
    /**
     * @param Notification $notification
     */
    public function add(Notification $notification);
}
