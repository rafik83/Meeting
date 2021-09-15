<?php

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

    /** @var int */
    public $sheetFromid;

    /** @var int */
    public $sheetToId;

    /**
     * @param int  $requestId
     * @param int  $slotId
     * @param int  $spotId
     * @param bool $lockedSlot
     * @param bool $lockedSpot
     * @param int  $sheetFromId
     * @param int  $sheetToId
     */
    public function __construct($requestId, $slotId, $spotId, $lockedSlot, $lockedSpot, $sheetFromId, $sheetToId)
    {
        $this->requestId   = $requestId;
        $this->slotId      = $slotId;
        $this->spotId      = $spotId;
        $this->lockedSlot  = $lockedSlot;
        $this->lockedSpot  = $lockedSpot;
        $this->sheetFromid = $sheetFromId;
        $this->sheetToId   = $sheetToId;
    }
}
