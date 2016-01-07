<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationRepositoryInterface
{
    /**
     * @param Notification $notification
     */
    public function add(Notification $notification);

    /**
     * @param int  $eventId
     * @param User $user
     *
     * @return Notification[]
     */
    public function getUnreadByEventAndUser($eventId, User $user);
}
