<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Request;

class SendCodeForm
{
    /** @var Request */
    public $request;

    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /**
     * @param Request $request
     * @param User    $user
     * @param Event   $event
     */
    public function __construct(Request $request, User $user, Event $event)
    {
        $this->request = $request;
        $this->user = $user;
        $this->event = $event;
    }
}
