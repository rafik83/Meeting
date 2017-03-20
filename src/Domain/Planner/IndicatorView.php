<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planner;

class IndicatorView
{
    /**
     * "Nombre de slots totals de l'event"
     *
     * @var int
     */
    public $slotTotal;

    /**
     * "Nombre de slots indisponibles de tous participants d'une fiche"
     * "(indisponibles ou participant à une conférence, ou indispo de masse)"
     *
     * @var int
     */
    public $unavailabilitiesCount;

    /**
     * "Nombre de participants de la fiche"
     *
     * @var int
     */
    public $participantsCount;

    /**
     * Nombre de planning de la fiche
     *
     * @var int
     */
    public $sheetsPlanningQuantity;

    /**
     * "Nombre de demandes de RDV"
     *
     * @var int
     */
    public $meetingRequestsCount;

    /**
     * "Nombre de slots en tenant compte du nombre de planning"
     *
     * @var int
     */
    public $slotCount;

    /**
     * "Nombre de slots en tenant du nombre de participants"
     *
     * @var int
     */
    public $slotsParticipantsCount;

    /**
     * "Nombre de slots où les participants sont disponibles"
     *
     * @var int
     */
    public $availableSlotsCount;

    /**
     * "Nombre de RDV maximum possibles"
     *
     * @var int
     */
    public $possibleMeetingsQuantity;

    /**
     * "Nombre de slots utilisables"
     *
     * @var int
     */
    public $usableSlots;

    /**
     * "Nombre de propositions reçues à valider"
     *
     * @var int
     */
    public $pendingPropositionCount;

    /**
     * @param int $slotTotal
     * @param int $participantsCount
     * @param int $unavailabilitiesCount
     * @param int $sheetsPlanningQuantity
     * @param int $meetingRequestsCount
     * @param int $pendingPropositionCount
     */
    public function __construct(
        $slotTotal,
        $participantsCount,
        $unavailabilitiesCount,
        $sheetsPlanningQuantity,
        $meetingRequestsCount,
        $pendingPropositionCount
    ) {
        $this->slotTotal               = $slotTotal;
        $this->participantsCount       = $participantsCount;
        $this->unavailabilitiesCount   = $unavailabilitiesCount;
        $this->sheetsPlanningQuantity  = $sheetsPlanningQuantity;
        $this->meetingRequestsCount    = $meetingRequestsCount;
        $this->pendingPropositionCount = $pendingPropositionCount;

        $this->slotCount                = $slotTotal * $sheetsPlanningQuantity;
        $this->slotsParticipantsCount   = $slotTotal * $participantsCount;

        $this->availableSlotsCount      = $this->slotsParticipantsCount - $unavailabilitiesCount;
        $this->possibleMeetingsQuantity = max(0, min($meetingRequestsCount, $this->slotCount, $this->availableSlotsCount));
        $this->usableSlots              = max(0, min($this->slotCount, $this->availableSlotsCount));
    }
}
