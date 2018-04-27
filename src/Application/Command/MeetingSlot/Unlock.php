<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class Unlock
{
    /**
     * @var MeetingSlot
     */
    public $meetingSlot;

    /**
     * Lock constructor.
     *
     * @param MeetingSlot $meetingSlot
     */
    public function __construct(MeetingSlot $meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;
    }
}
