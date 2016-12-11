<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Category
     */
    public $category;

    /**
     * @var string
     */
    public $name;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $blocking;

    /**
     * @var array
     */
    public $translations;

    /**
     * @param Event     $event
     * @param Event\Day $day
     */
    public function __construct(Event $event, Event\Day $day)
    {
        $this->event        = $event;
        $this->begin        = $day->getStartTime();
        $this->end          = $day->getStartTime();
        $this->blocking     = true;
        $this->translations = [];

        foreach ($this->event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'       => '',
                'description' => '',
            ];
        }
    }
}
