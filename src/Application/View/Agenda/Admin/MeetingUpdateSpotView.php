<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class MeetingUpdateSpotView
{
    /** @var int */
    public $meetingId;

    /** @var int */
    public $spotId;

    /** @var bool */
    public $blockedSlot;

    /** @var bool */
    public $blockedSpot;

    /** @var SpotView[] */
    public $availableSpots;

    /**
     * @param int        $meetingId
     * @param int        $spotId
     * @param bool       $blockedSlot
     * @param bool       $blockedSpot
     * @param SpotView[] $availableSpots
     */
    public function __construct($meetingId, $spotId, $blockedSlot, $blockedSpot, array $availableSpots)
    {
        $this->meetingId      = $meetingId;
        $this->spotId         = $spotId;
        $this->blockedSlot    = $blockedSlot;
        $this->blockedSpot    = $blockedSpot;
        $this->availableSpots = $availableSpots;
    }
}
