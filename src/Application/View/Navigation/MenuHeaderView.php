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
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @var Sheet
     */
    private $sheet;

    /**
     * MenuHeaderView constructor.
     *
     * @param Event      $event
     * @param array      $localeRoutes
     * @param Sheet|null $sheet
     * @param bool       $notification
     */
    public function __construct(Event $event, array $localeRoutes, Sheet $sheet = null, $notification = false)
    {
        $this->notification = $notification;
        $this->event        = $event;
        $this->localeRoutes = $localeRoutes;
        $this->sheet        = $sheet;
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

    /**
     * @return bool
     */
    public function hasSheet()
    {
        return $this->sheet !== null;
    }
}
