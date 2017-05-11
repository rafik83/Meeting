<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingOnOtherSheetView
{
    /** @var MeetingSlot */
    public $slot;

    /** @var string */
    public $type;

    /** @var string */
    public $sheetTitle;

    /** @var int */
    public $sheetId;

    /**
     * MeetingOnOtherSheetView constructor.
     *
     * @param MeetingSlot $slot
     * @param string      $type
     * @param string      $sheetTitle
     * @param int         $sheetId
     */
    public function __construct(MeetingSlot $slot, $type, $sheetTitle, $sheetId)
    {
        $this->slot       = $slot;
        $this->type       = $type;
        $this->sheetTitle = $sheetTitle;
        $this->sheetId    = $sheetId;
    }
}
