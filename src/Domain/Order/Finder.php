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

class Finder
{
    /**
     * @param Admin $admin
     *
     * @return bool
     */
    public static function isAllowToFind(Admin $admin)
    {
        return $admin->isPartner();
    }
}
