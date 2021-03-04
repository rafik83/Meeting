<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\Slot\SlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

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

    /** @var ParticipantView[] */
    public $participants;

    /** @var int[] */
    public array $meetingParticipants;

    /** @var SlotView[] */
    public array $meetingSlots;

    /** @var array<int, int[]> */
    public array $currentSheetAvailableSlotIds;

    /** @var int */
    public $slotId;

    /**
     * @param int               $meetingId
     * @param int               $spotId
     * @param bool              $blockedSlot
     * @param bool              $blockedSpot
     * @param SpotView[]        $availableSpots
     * @param ParticipantView[] $participants,
     * @param int[]             $meetingParticipants
     * @param SlotView[]        $meetingSlots
     * @param array             $currentSheetAvailableSlotIds
     * @param int               $slotId
     */
    public function __construct(
        $meetingId,
        $spotId,
        $blockedSlot,
        $blockedSpot,
        array $availableSpots,
        array $participants,
        array $meetingParticipants,
        array $meetingSlots,
        array $currentSheetAvailableSlotIds,
        $slotId
    ) {
        $this->meetingId = $meetingId;
        $this->spotId = $spotId;
        $this->blockedSlot = $blockedSlot;
        $this->blockedSpot = $blockedSpot;
        $this->availableSpots = $availableSpots;
        $this->participants = $participants;
        $this->meetingParticipants = $meetingParticipants;
        $this->meetingSlots = $meetingSlots;
        $this->currentSheetAvailableSlotIds = $currentSheetAvailableSlotIds;
        $this->slotId = $slotId;
    }
}
