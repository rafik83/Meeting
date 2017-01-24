<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class UpdateSlot
{
    /** @var Meeting */
    public $meeting;

    /** @var MeetingSlot */
    public $slot;

    /**
     * @param Meeting     $meeting
     * @param MeetingSlot $slot
     */
    public function __construct(Meeting $meeting, MeetingSlot $slot)
    {
        $this->meeting = $meeting;
        $this->slot    = $slot;
    }
}
