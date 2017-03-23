<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SpotSlotView extends AbstractSlotView
{
    /**
     * @var array
     */
    public $meetings = [];

    /**
     * SpotSlotView constructor.
     *
     * @param MeetingSlot $slot
     * @param string      $type
     * @param array       $meetings
     */
    public function __construct(MeetingSlot $slot, $type, array $meetings = [])
    {
        parent::__construct($slot, $type);

        $this->meetings = $meetings;
    }
}
