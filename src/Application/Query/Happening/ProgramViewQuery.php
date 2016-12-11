<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;

class ProgramViewQuery
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
     * @var int|null
     */
    public $day;

    /**
     * @var Category|null
     */
    public $category;

    /**
     *  ProgramViewQuery constructor
     *
     * @param Event         $event
     * @param string        $locale
     * @param Category|null $category
     * @param int|null      $day
     */
    public function __construct(Event $event, $locale, Category $category = null, $day = null)
    {
        $this->event    = $event;
        $this->locale   = $locale;
        $this->day      = $day;
        $this->category = $category;
    }
}
