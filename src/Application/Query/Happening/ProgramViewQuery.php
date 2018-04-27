<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

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
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     *  ProgramViewQuery constructor
     *
     * @param Event         $event
     * @param Sheet         $sheet
     * @param User          $user
     * @param string        $locale
     * @param Category|null $category
     * @param int|null      $day
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        $locale,
        Category $category = null,
        $day = null
    ) {
        $this->event    = $event;
        $this->user     = $user;
        $this->sheet    = $sheet;
        $this->locale   = $locale;
        $this->day      = $day;
        $this->category = $category;
    }
}
