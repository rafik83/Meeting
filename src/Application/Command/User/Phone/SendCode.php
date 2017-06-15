<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SendCode
{
    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var string */
    private $phone;

    /** @var bool */
    private $accepted;

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     */
    public function __construct(User $user, Event $event, $phone)
    {
        $this->user = $user;
        $this->event = $event;
        $this->phone = $phone;
        $this->accepted = false;
    }
}
