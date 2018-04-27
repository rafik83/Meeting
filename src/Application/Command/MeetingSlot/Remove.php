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

class Remove
{
    /**
     * @var MeetingSlot
     */
    public $meetingSlot;

    /**
     * Remove constructor.
     *
     * @param MeetingSlot $meetingSlot
     */
    public function __construct(MeetingSlot $meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;
    }
}
