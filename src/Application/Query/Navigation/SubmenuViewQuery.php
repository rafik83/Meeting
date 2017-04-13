<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SubmenuViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var null|Sheet
     */
    public $sheet;

    /**
     * @var null|User
     */
    public $user;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $route;

    /**
     * @param Event      $event
     * @param Sheet|null $sheet
     * @param User       $user
     * @param string     $locale
     * @param string     $route
     */
    public function __construct(Event $event, Sheet $sheet = null, User $user = null, $locale, $route)
    {
        $this->event  = $event;
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->locale = $locale;
        $this->route  = $route;
    }
}
