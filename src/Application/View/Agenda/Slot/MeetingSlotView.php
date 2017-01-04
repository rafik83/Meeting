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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class MeetingSlotView extends AbstractSlotView
{
    /**
     * @var int
     */
    public $meetingId;

    /**
     * @var Sheet
     */
    public $sheetMet;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var bool
     */
    public $hasNoPreference;

    /**
     * MeetingView constructor.
     *
     * @param MeetingSlot $slot
     * @param Spot        $spot
     * @param Sheet       $sheetMet
     * @param int         $meetingId
     * @param bool        $hasNoPreference
     */
    public function __construct(
        MeetingSlot $slot,
        Spot $spot,
        Sheet $sheetMet,
        $meetingId,
        $hasNoPreference
    ) {
        parent::__construct($slot);

        $this->spot            = $spot;
        $this->meetingId       = $meetingId;
        $this->sheetMet        = $sheetMet;
        $this->hasNoPreference = $hasNoPreference;
    }
}
