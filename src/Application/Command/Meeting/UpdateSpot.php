<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

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

    /**
     * @param Meeting $meeting
     * @param Spot    $spot
     * @param bool    $blockedSlot
     * @param bool    $blockedSpot
     */
    public function __construct(Meeting $meeting, Spot $spot, $blockedSlot, $blockedSpot)
    {
        $this->meeting     = $meeting;
        $this->spot        = $spot;
        $this->blockedSlot = $blockedSlot;
        $this->blockedSpot = $blockedSpot;
    }

    /**
     * @return boolean
     */
    public function isBlockedSlot()
    {
        return $this->blockedSlot;
    }

    /**
     * @return boolean
     */
    public function isBlockedSpot()
    {
        return $this->blockedSpot;
    }
}
