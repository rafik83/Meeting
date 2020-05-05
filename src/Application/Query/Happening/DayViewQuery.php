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
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;

class DayViewQuery
{
    /** @var Event\Day */
    public $eventDay;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var Category|null */
    public $category;

    /** @var Mass[] */
    public $masses;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /**
     * @param Event         $event
     * @param Sheet         $sheet
     * @param User          $user
     * @param Event\Day     $eventDay
     * @param string        $locale
     * @param Category|null $category
     * @param Mass[]        $masses
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        Event\Day $eventDay,
        string $locale,
        Category $category = null,
        array $masses = []
    ) {
        $this->locale = $locale;
        $this->event = $event;
        $this->eventDay = $eventDay;
        $this->category = $category;
        $this->masses = $masses;
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
