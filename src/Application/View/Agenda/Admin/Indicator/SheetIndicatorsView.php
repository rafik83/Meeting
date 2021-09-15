<?php

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
     * "La fiche a des créneaux disponibles"
     *
     * @var bool
     */
    public $hasAvailableSlots;

    /**
     * "A encore des demandes/propositions validées et non placées"
     *
     * @var bool
     */
    public $hasValidatedRequestNotScheduled;

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
        $this->hasNotSentMeetingRequest = 0 === $this->countRequest;
        $this->hasMeetingToApprove      = $this->countPendingPropositions > 0;

        $this->hasNotEnoughAvailableSlot       = $this->calculateHasNotEnoughAvailableSlot();
        $this->hasAvailableSlots               = $this->calculateHasAvailableSlots();
        $this->hasValidatedRequestNotScheduled = $this->calculateHasValidatedRequestNotScheduled();
    }

    /**
     * "La fiche a-t-elle assez de creneaux disponible"
     *
     * @return bool
     */
    private function calculateHasNotEnoughAvailableSlot()
    {
        if (0 === $this->usableSlots) {
            return true;
        }

        return (($this->countValidatedRequest - $this->countPlacedMeetings) / $this->usableSlots) > 1;
    }

    /**
     * "La fiche a des créneaux disponibles"
     *
     * @return bool
     */
    private function calculateHasAvailableSlots()
    {
        return ($this->usableSlots - $this->countPlacedMeetings) > 0;
    }

    /**
     * "A encore des demandes/propositions validées et non placées"
     *
     * @return bool
     */
    private function calculateHasValidatedRequestNotScheduled()
    {
        return ($this->countValidatedRequest - $this->countPlacedMeetings) > 0;
    }
}
