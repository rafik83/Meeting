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

class AvailableMeetingView
{
    /**
     * @var MeetingSlot
     */
    public $slot;

    /**
     * @var Sheet
     */
    public $fromSheet;

    /**
     * @var Sheet
     */
    public $toSheet;

    /**
     * @var Participant[]
     */
    public $fromParticipants = [];

    /**
     * @var Participant[]
     */
    public $toParticipants = [];

    /**
     * @var bool
     */
    public $fromSheetHasNoPreference;

    /**
     * @var bool
     */
    public $toSheetHasNoPreference;

    /**
     *
     * @param MeetingSlot   $slot
     * @param Sheet         $fromSheet
     * @param Sheet         $toSheet
     * @param Participant[] $fromParticipants
     * @param Participant[] $toParticipants
     * @param bool          $fromSheetHasNoPreference
     * @param bool          $toSheetHasNoPreference
     */
    public function __construct(
        MeetingSlot $slot,
        Sheet $fromSheet,
        Sheet $toSheet,
        array $fromParticipants,
        array $toParticipants,
        bool $fromSheetHasNoPreference,
        bool $toSheetHasNoPreference
    ) {
        $this->slot                     = $slot;
        $this->fromSheet                = $fromSheet;
        $this->toSheet                  = $toSheet;
        $this->fromParticipants         = $fromParticipants;
        $this->toParticipants           = $toParticipants;
        $this->fromSheetHasNoPreference = $fromSheetHasNoPreference;
        $this->toSheetHasNoPreference   = $toSheetHasNoPreference;
    }
}
