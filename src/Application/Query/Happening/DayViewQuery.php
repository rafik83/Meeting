<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Happening\Category;

class DayViewQuery
{
    /**
     * @var Event\Day
     */
    public $eventDay;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Category|null
     */
    public $category;

    /**
     * @var Mass[]
     */
    public $masses;

    /**
     * @param Event         $event
     * @param Event\Day     $eventDay
     * @param string        $locale
     * @param Category|null $category
     * @param Mass[]        $masses
     */
    public function __construct(Event $event, Event\Day $eventDay, $locale, Category $category = null, array $masses)
    {
        $this->locale   = $locale;
        $this->event    = $event;
        $this->eventDay = $eventDay;
        $this->category = $category;
        $this->masses   = $masses;
    }
}
