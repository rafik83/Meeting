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

    public int $metParticipantsCount;

    public function __construct(
        int $meetingId,
        int $spotId,
        bool $blockedSlot,
        bool $blockedSpot,
        array $availableSpots,
        array $participants,
        array $meetingParticipants,
        array $meetingSlots,
        array $currentSheetAvailableSlotIds,
        int $slotId,
        int $metParticipantsCount
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
        $this->metParticipantsCount = $metParticipantsCount;
    }
}
