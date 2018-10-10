<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

class ParticipantPresenceView
{
    /** @var int */
    public $id;

    /** @var bool */
    public $present;

    public function __construct(int $id, bool $present = false)
    {
        $this->id = $id;
        $this->present = $present;
    }
}
