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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class MenuViewQuery
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
     * @param Event      $event
     * @param null|Sheet $sheet
     * @param null|User  $user
     * @param string     $locale
     */
    public function __construct(Event $event, Sheet $sheet = null, User $user = null, $locale)
    {
        $this->event  = $event;
        $this->user   = $user;
        $this->locale = $locale;
        $this->sheet = $sheet;
    }
}
