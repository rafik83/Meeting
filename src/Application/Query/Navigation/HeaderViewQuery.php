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

class HeaderViewQuery
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
     * @var string
     */
    public $route;

    /**
     * @var array
     */
    public $routeParameters;

    /**
     * @var null|Sheet
     */
    public $sheet;

    /**
     * @param Event      $event
     * @param null|Sheet $sheet
     * @param string     $locale
     * @param null|User  $user
     * @param string     $route
     * @param array      $routeParameters
     * @param bool       $registration
     */
    public function __construct(
        Event $event,
        Sheet $sheet = null,
        $locale,
        User $user = null,
        $route,
        array $routeParameters,
        $registration
    ) {
        $this->event           = $event;
        $this->sheet           = $sheet;
        $this->user            = $user;
        $this->locale          = $locale;
        $this->registration    = $registration;
        $this->route           = $route;
        $this->routeParameters = $routeParameters;
    }
}
