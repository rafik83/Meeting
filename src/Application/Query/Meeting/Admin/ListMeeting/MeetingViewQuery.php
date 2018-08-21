<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingViewQuery implements Query
{
    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $locale;

    public function __construct(Meeting $meeting, string $locale)
    {
        $this->meeting = $meeting;
        $this->locale = $locale;
    }
}
