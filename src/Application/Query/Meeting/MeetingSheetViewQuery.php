<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class MeetingSheetViewQuery
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
     * @var User
     */
    public $user;

    /**
     * MeetingSheetViewQuery constructor.
     *
     * @param User   $user
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(User $user, Event $event, $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
        $this->user   = $user;
    }
}
