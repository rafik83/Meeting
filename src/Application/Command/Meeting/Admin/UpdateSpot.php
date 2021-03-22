<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class UpdateSpot implements Command
{
    /** @var Meeting */
    public $meeting;

    /** @var Spot */
    public $spot;

    /** @var bool */
    public $blockedSlot;

    /** @var bool */
    public $blockedSpot;

    /** @var bool */
    public $visio;

    public Sheet $sheet;

    public MeetingSlot $slot;

    /** @var Participant[] */
    public $participants;

    /**
     * @param Meeting       $meeting
     * @param Spot          $spot
     * @param bool          $blockedSlot
     * @param bool          $blockedSpot
     * @param bool          $visio
     * @param Sheet         $sheet
     * @param MeetingSlot   $slot
     * @param Participant[] $participants
     */
    public function __construct(Meeting $meeting, Spot $spot, $blockedSlot, $blockedSpot, $visio = false, Sheet $sheet, MeetingSlot $slot, array $participants)
    {
        $this->meeting     = $meeting;
        $this->spot        = $spot;
        $this->blockedSlot = $blockedSlot;
        $this->blockedSpot = $blockedSpot;
        $this->visio       = $visio;
        $this->sheet = $sheet;
        $this->slot = $slot;
        $this->participants = $participants;
    }

    /**
     * @return bool
     */
    public function isBlockedSlot()
    {
        return $this->blockedSlot;
    }

    /**
     * @return bool
     */
    public function isBlockedSpot()
    {
        return $this->blockedSpot;
    }
}
