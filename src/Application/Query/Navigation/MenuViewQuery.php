<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var string
     */
    public $locale;

    /**
     * @var null|Sheet
     */
    public $sheet;

    /**
     * @var null|User
     */
    public $user;

    /**
     * @param Event      $event
     * @param string     $locale
     * @param null|Sheet $sheet
     * @param null|User  $user
     */
    public function __construct(Event $event, $locale, Sheet $sheet = null, User $user = null)
    {
        $this->event  = $event;
        $this->locale = $locale;
        $this->sheet  = $sheet;
        $this->user   = $user;
    }
}
