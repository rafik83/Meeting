<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class AvailableSlotView
{
    /**
     * @var MeetingSlot
     */
    public $slot;

    /**
     * @param MeetingSlot $slot
     */
    public function __construct(MeetingSlot $slot)
    {
        $this->slot = $slot;
    }
}
