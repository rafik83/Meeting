<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingSlotView extends AbstractSlotView
{
    /**
     * @var int
     */
    public $meetingId;

    /**
     * @var int
     */
    public $sheetMetId;

    /**
     * @var string
     */
    public $sheetMetTitle;

    /**
     * @var int
     */
    public $spotId;

    /**
     * @var bool
     */
    public $hasNoPreference;

    /**
     * @var string
     */
    public $spotReference;

    /**
     * @var bool
     */
    public $blockedSpot;

    /**
     * @var bool
     */
    public $blockedSlot;

    /**
     * MeetingView constructor.
     *
     * @param MeetingSlot $slot
     * @param string      $type
     * @param int         $spotId
     * @param string      $spotReference
     * @param int         $sheetMetId
     * @param string      $sheetMetTitle
     * @param int         $meetingId
     * @param bool        $hasNoPreference
     * @param bool        $blockedSpot
     * @param bool        $blockedSlot
     */
    public function __construct(
        MeetingSlot $slot,
        $type,
        $spotId,
        $spotReference,
        $sheetMetId,
        $sheetMetTitle,
        $meetingId,
        $hasNoPreference,
        $blockedSpot,
        $blockedSlot
    ) {
        parent::__construct($slot, $type);

        $this->spotId          = $spotId;
        $this->meetingId       = $meetingId;
        $this->sheetMetId      = $sheetMetId;
        $this->sheetMetTitle   = $sheetMetTitle;
        $this->hasNoPreference = $hasNoPreference;
        $this->spotReference   = $spotReference;
        $this->blockedSpot     = $blockedSpot;
        $this->blockedSlot     = $blockedSlot;
    }
}
