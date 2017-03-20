<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner\Result;

class MeetingResult
{
    /** @var SpotResult|null */
    public $spot;

    /** @var SlotResult|null */
    public $slot;

    /** @var int */
    public $requestId;

    /** @var SheetResult */
    public $sheetFrom;

    /** @var SheetResult */
    public $sheetTo;

    /** @var ParticipantResult[] */
    public $participants;

    /**
     * @param ParticipantResult $participantResult
     */
    public function addParticipant(ParticipantResult $participantResult)
    {
        $this->participants[] = $participantResult;
    }
}
