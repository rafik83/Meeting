<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingOnOtherSheetView extends AbstractSlotView
{
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
        parent::__construct($slot, $type);

        $this->sheetTitle = $sheetTitle;
        $this->sheetId    = $sheetId;
    }
}
