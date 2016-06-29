<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class PreRegisterEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $locale;

    /**
     * PreRegisterEvent constructor.
     *
     * @param $event
     * @param $user
     * @param $locale
     */
    public function __construct($event, $user, $locale)
    {
        $this->event  = $event;
        $this->user   = $user;
        $this->locale = $locale;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

}