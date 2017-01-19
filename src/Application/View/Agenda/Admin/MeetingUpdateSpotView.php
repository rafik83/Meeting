<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class MeetingUpdateSpotView
{
    /** @var int */
    public $spotId;

    /** @var bool */
    public $blockedSlot;

    /** @var bool */
    public $blockedSpot;

    /** @var SpotView[] */
    public $availableSpots;

    /**
     * @param int        $spotId
     * @param bool    $blockedSlot
     * @param bool    $blockedSpot
     * @param SpotView[]   $availableSpots
     */
    public function __construct($spotId, $blockedSlot, $blockedSpot, array $availableSpots)
    {
        $this->spotId         = $spotId;
        $this->blockedSlot    = $blockedSlot;
        $this->blockedSpot    = $blockedSpot;
        $this->availableSpots = $availableSpots;
    }
}
