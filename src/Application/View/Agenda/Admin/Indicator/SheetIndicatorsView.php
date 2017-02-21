<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Indicator;

class SheetIndicatorsView
{
    /**
     * @var int
     */
    public $countRequest;

    /**
     * @var int
     */
    public $countProposition;

    /**
     * @var int
     */
    public $countValidatedRequest;

    /**
     * @var int
     */
    public $countSlots;

    /**
     * @var int
     */
    public $usableSlots;

    /**
     * @var int
     */
    public $countPlacedMeetings;

    /**
     * @var int
     */
    public $countPendingPropositions;

    /**
     * @var bool
     */
    public $hasNotSentMeetingRequest;

    /**
     * @var bool
     */
    public $hasMeetingToApprove;

    /**
     * @var bool
     */
    public $hasNotEnoughAvailableSlot;

    /**
     * @param int $countRequest
     * @param int $countProposition
     * @param int $countValidatedRequest
     * @param int $countSlots
     * @param int $usableSlots
     * @param int $countPlacedMeetings
     * @param int $countPendingPropositions
     */
    public function __construct(
        $countRequest = 0,
        $countProposition = 0,
        $countValidatedRequest = 0,
        $countSlots = 0,
        $usableSlots = 0,
        $countPlacedMeetings = 0,
        $countPendingPropositions = 0
    ) {
        $this->countRequest             = $countRequest;
        $this->countProposition         = $countProposition;
        $this->countValidatedRequest    = $countValidatedRequest;
        $this->countSlots               = $countSlots;
        $this->usableSlots              = $usableSlots;
        $this->countPlacedMeetings      = $countPlacedMeetings;
        $this->countPendingPropositions = $countPendingPropositions;
        $this->hasNotSentMeetingRequest = $this->countRequest === 0;
        $this->hasMeetingToApprove      = $this->countPendingPropositions > 0;

        $this->hasNotEnoughAvailableSlot = $this->calculateHasNotEnoughAvailableSlot();
    }


    /**
     * "La fiche a-t-elle assez de creneaux disponible"
     *
     * @return bool
     */
    private function calculateHasNotEnoughAvailableSlot()
    {
        if ($this->usableSlots === 0) {
            return true;
        }

        return (($this->countValidatedRequest - $this->countPlacedMeetings) / $this->usableSlots) > 1;
    }
}
