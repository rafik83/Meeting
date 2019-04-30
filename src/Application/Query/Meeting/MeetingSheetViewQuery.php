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

class MeetingSheetViewQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(Event $event, User $user, Sheet $sheet, string $locale)
    {
        $this->event = $event;
        $this->user = $user;
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
