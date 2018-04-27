<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

class SpotMeetingSlotView
{
    /**
     * @var int
     */
    public $fromSheetId;

    /**
     * @var string
     */
    public $fromSheetTitle;

    /**
     * @var int
     */
    public $toSheetId;

    /**
     * @var string
     */
    public $toSheetTitle;

    /**
     * SpotMeetingSlotView constructor.
     *
     * @param int    $fromSheetId
     * @param string $fromSheetTitle
     * @param int    $toSheetId
     * @param string $toSheetTitle
     */
    public function __construct($fromSheetId, $fromSheetTitle, $toSheetId, $toSheetTitle)
    {
        $this->fromSheetId    = $fromSheetId;
        $this->fromSheetTitle = $fromSheetTitle;
        $this->toSheetId      = $toSheetId;
        $this->toSheetTitle   = $toSheetTitle;
    }
}
