<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    public $locked;

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
     * @param bool        $locked
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
        $locked
    ) {
        parent::__construct($slot, $type);

        $this->spotId          = $spotId;
        $this->meetingId       = $meetingId;
        $this->sheetMetId      = $sheetMetId;
        $this->sheetMetTitle   = $sheetMetTitle;
        $this->hasNoPreference = $hasNoPreference;
        $this->spotReference   = $spotReference;
        $this->locked          = $locked;
    }
}
