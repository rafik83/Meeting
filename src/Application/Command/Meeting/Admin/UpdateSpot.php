<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Spot;

class UpdateSpot
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

    /**
     * @param Meeting $meeting
     * @param Spot    $spot
     * @param bool    $blockedSlot
     * @param bool    $blockedSpot
     * @param bool    $visio
     */
    public function __construct(Meeting $meeting, Spot $spot, $blockedSlot, $blockedSpot, $visio = false)
    {
        $this->meeting     = $meeting;
        $this->spot        = $spot;
        $this->blockedSlot = $blockedSlot;
        $this->blockedSpot = $blockedSpot;
        $this->visio       = $visio;
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
