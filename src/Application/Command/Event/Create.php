<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Admin;

class Create extends AbstractEvent
{
    public $admin;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin    = $admin;
        $this->timeZone = 'Europe/Paris';
        $this->country  = 'FR';
    }
}
