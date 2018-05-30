<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class GetAllowedTypesByAdminQuery implements Query
{
    /** @var Admin */
    public $admin;

    /** @var Event */
    public $event;

    public function __construct(Admin $admin, Event $event)
    {
        $this->admin = $admin;
        $this->event = $event;
    }
}
