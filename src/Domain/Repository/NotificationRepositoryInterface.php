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
use Proximum\Vimeet\Domain\Model\Sheet;

interface NotificationRepositoryInterface
{
    /**
     * @param Notification $notification
     */
    public function add(Notification $notification);

    /**
     * @param Notification $notification
     */
    public function set(Notification $notification);

    /**
     * @param Sheet  $sheet
     * @param string $type
     */
    public function removeByType(Sheet $sheet, $type);

    /**
     * @param Sheet  $sheet
     * @param string $type
     *
     * @return Notification
     */
    public function findByType(Sheet $sheet, $type);
}
