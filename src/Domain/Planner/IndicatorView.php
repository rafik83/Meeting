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
     * Nombre de slots totals de l'event
     * @var int
     */
    public $slotTotal;

    /**
     * nb de slots indisponibles de tous participants d'une fiche
     * (indisponibles ou participant à une conférence, ou indispo de masse)
     *
     * @var int
     */
    public $unavailabilitiesCount;

    /**
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
     * Nombre de demandes de RDV
     *
     * @var int
     */
    public $meetingRequestsCount;

    /**
     * Nombre de slots en tenant compte du nombre de planning
     *
     * @var int
     */
    public $slotCount;

    /**
     * Nombre de slots (en tenant du nb de participants)
     *
     * @var int
     */
    public $slotsParticipantsCount;

    /**
     * Nombre de slots où les participants sont disponibles
     *
     * @var int
     */
    public $availableSlotsCount;

    /**
     * Nombre de slots utilisables
     *
     * @var int
     */
    public $possibleMeetingsQuantity;

    /**
     * @param int $slotTotal
     * @param int $participantsCount
     * @param int $unavailabilitiesCount
     * @param int $sheetsPlanningQuantity
     * @param int $meetingRequestsCount
     */
    public function __construct(
        $slotTotal,
        $participantsCount,
        $unavailabilitiesCount,
        $sheetsPlanningQuantity,
        $meetingRequestsCount
    ) {
        $this->slotTotal              = $slotTotal;
        $this->participantsCount      = $participantsCount;
        $this->unavailabilitiesCount  = $unavailabilitiesCount;
        $this->sheetsPlanningQuantity = $sheetsPlanningQuantity;
        $this->meetingRequestsCount   = $meetingRequestsCount;

        $this->slotCount                = $slotTotal * $sheetsPlanningQuantity;
        $this->slotsParticipantsCount   = $slotTotal * $participantsCount;
        $this->availableSlotsCount      = $this->slotsParticipantsCount - $unavailabilitiesCount;
        $this->possibleMeetingsQuantity = min($meetingRequestsCount, $this->slotCount, $this->availableSlotsCount);
    }
}
