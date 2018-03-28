<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class LeniUserCustomDataQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Type */
    public $type;

    public function __construct(Event $event, User $user, Type $type)
    {
        $this->user = $user;
        $this->type = $type;
        $this->event = $event;
    }
}
