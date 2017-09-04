<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\MeetingPropositionFromAvailableSheets;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingPropositionFromAvailableSheetsQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var MeetingSlot */
    public $meetingSlot;

    /**
     * @param Sheet       $sheet
     * @param MeetingSlot $meetingSlot
     */
    public function __construct(Sheet $sheet, MeetingSlot $meetingSlot)
    {
        $this->sheet = $sheet;
        $this->meetingSlot = $meetingSlot;
    }
}
