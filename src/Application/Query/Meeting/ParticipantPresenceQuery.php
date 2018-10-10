<?php

/*
 * This file is part of the PhpStorm project.
 *
 * Copyright (C) PhpStorm
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class ParticipantPresenceQuery implements Query
{
    /** @var MeetingSlot */
    public $meetingSlot;

    public function __construct(MeetingSlot $meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;
    }
}
