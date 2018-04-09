<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetCustomData
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var array */
    public $data;

    public function __construct(Event $event, User $user, array $data)
    {
        $this->event = $event;
        $this->user = $user;
        $this->data = $data;
    }
}
