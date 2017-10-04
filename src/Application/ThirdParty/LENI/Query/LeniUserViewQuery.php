<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Query;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class LeniUserViewQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Sheet[] */
    public $sheets;

    /**
     * @param Event   $event
     * @param User    $user
     * @param Sheet[] $sheets
     */
    public function __construct(Event $event, User $user, array $sheets)
    {
        $this->event = $event;
        $this->user = $user;
        $this->sheets = $sheets;
    }
}
