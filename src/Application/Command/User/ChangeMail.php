<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\EventView;

class ChangeMail
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $mail;

    /**
     * @var EventView
     */
    public $eventView;

    /**
     * @param User      $user
     * @param EventView $eventView
     */
    public function __construct(User $user, EventView $eventView)
    {
        $this->user      = $user;
        $this->eventView = $eventView;
    }
}
