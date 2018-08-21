<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class MeetingRequestListViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /** @var array */
    public $filters;

    /** @var array */
    public $slotsToFilter;

    /** @var Event */
    public $event;

    /** @var bool */
    public $showCategory;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     * @param array  $filters
     * @param array  $slotsToFilter
     * @param bool   $showCategory
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        $locale,
        array $filters = [],
        array $slotsToFilter = [],
        bool $showCategory = false
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->locale = $locale;
        $this->filters = $filters;
        $this->slotsToFilter = $slotsToFilter;
        $this->showCategory = $showCategory;
    }
}
