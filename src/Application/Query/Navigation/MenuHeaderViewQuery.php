<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class MenuHeaderViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var User|null
     */
    public $user;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var bool
     */
    public $registration;

    /**
     * MenuHeaderViewQuery constructor.
     *
     * @param Event     $event
     * @param string    $locale
     * @param User|null $user
     * @param bool      $registration
     */
    public function __construct(Event $event, $locale, User $user = null, $registration)
    {
        $this->event        = $event;
        $this->user         = $user;
        $this->locale       = $locale;
        $this->registration = $registration;
    }
}
