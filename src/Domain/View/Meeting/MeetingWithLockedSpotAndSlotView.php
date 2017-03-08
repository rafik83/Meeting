<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Meeting;

class MeetingWithLockedSpotAndSlotView
{
    /** @var int */
    public $requestId;

    /** @var int */
    public $slotId;

    /** @var int */
    public $spotId;

    /** @var bool */
    public $lockedSlot;

    /** @var bool */
    public $lockedSpot;

    /**
     * @param int  $requestId
     * @param int  $slotId
     * @param int  $spotId
     * @param bool $lockedSlot
     * @param bool $lockedSpot
     */
    public function __construct($requestId, $slotId, $spotId, $lockedSlot, $lockedSpot)
    {
        $this->requestId  = $requestId;
        $this->slotId     = $slotId;
        $this->spotId     = $spotId;
        $this->lockedSlot = $lockedSlot;
        $this->lockedSpot = $lockedSpot;
    }
}
