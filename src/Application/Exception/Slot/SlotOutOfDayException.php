<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotOutOfDayException extends SlotException
{
    /** @var MeetingSlot */
    public $slot;

    /**
     * @param MeetingSlot $slot
     */
    public function __construct(MeetingSlot $slot)
    {
        parent::__construct("", 0, null);

        $this->slot = $slot;
    }
}
