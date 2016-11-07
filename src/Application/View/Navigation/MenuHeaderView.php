<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

use Proximum\Vimeet\Domain\Model\Event;

class MenuHeaderView
{
    /**
     * @var bool
     */
    private $notification;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var array
     */
    private $localeRoutes;

    /**
     * MenuHeaderView constructor.
     *
     * @param Event $event
     * @param array $localeRoutes
     * @param bool  $notification
     */
    public function __construct(Event $event, array $localeRoutes, $notification = false)
    {
        $this->notification = $notification;
        $this->event        = $event;
        $this->localeRoutes = $localeRoutes;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function hasNotifications()
    {
        return $this->notification;
    }

    /**
     * @return array
     */
    public function getLocaleRoutes()
    {
        return $this->localeRoutes;
    }
}
