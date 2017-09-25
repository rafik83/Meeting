<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class AvailableSlotsBySheetView
{
    /**
     * @var Sheet $sheet
     */
    public $sheet;

    /**
     * @var bool
     */
    public $hasNoPreference;

    /**
     * @var AvailableSlotsParticipantView[]
     */
    public $availableSlotsByParticipant = [];

    /**
     * @var MeetingSlot[]
     */
    public $availableSlotsBySheet = [];

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @param Sheet $sheet
     * @param array $participants
     * @param bool  $hasNoPreference
     */
    public function __construct(Sheet $sheet, array $participants, bool $hasNoPreference)
    {
        $this->sheet           = $sheet;
        $this->hasNoPreference = $hasNoPreference;
        $this->participants    = $participants;
    }

    /**
     * @return bool
     */
    public function hasPreference(): bool
    {
        return $this->hasNoPreference === false;
    }
}
