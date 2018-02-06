<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Order;

class Finder
{
    /**
     * @param Admin $admin
     *
     * @return bool
     */
    public static function isAllowedToFind(Admin $admin): bool
    {
        return !$admin->isPartner();
    }

    /**
     * @param Admin $admin
     * @param Order $order
     *
     * @return bool
     */
    public static function isAllowedToAccess(Admin $admin, Order $order): bool
    {
        if (!self::isAllowedToFind($admin)) {
            return false;
        }

        if ($admin->hasAccessToAllEvent()) {
            return true;
        }

        return $admin->hasEvent($order->getSheet()->getEvent());
    }
}
