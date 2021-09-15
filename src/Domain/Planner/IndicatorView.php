<?php

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
     * "Nombre maximum de rendez-vous autorisé (pack de rdv)"
     *
     * @var int
     */
    public $maxMeetingAvailable;

    /**
     * "Nombre de slot en indispo de masse"
     *
     * @var int
     */
    public $massUnavailabilitiesCount;

    /**
     * Number of Meetings per planning defined per Type
     *
     * @var int|null
     */
    public $numberOfMeetingsPerPlanning;

    /**
     * Number of Meetings per Sheet
     *
     * @var int|null
     */
    public $numberMaxOfMeetingsPerSheet;

    public function __construct(
        int $slotTotal,
        int $participantsCount,
        int $unavailabilitiesCount,
        int $sheetsPlanningQuantity,
        int $meetingRequestsCount,
        int $pendingPropositionCount,
        int $massUnavailabilitiesCount,
        ?int $numberOfMeetingsPerPlanning,
        ?int $numberMaxOfMeetingsPerSheet
    ) {
        if (0 === $participantsCount) {
            throw new \InvalidArgumentException('ParticipantsCount must be > 0');
        }

        $this->slotTotal = $slotTotal;
        $this->participantsCount = $participantsCount;
        $this->unavailabilitiesCount = $unavailabilitiesCount;
        $this->sheetsPlanningQuantity = $sheetsPlanningQuantity;
        $this->meetingRequestsCount = $meetingRequestsCount;
        $this->pendingPropositionCount = $pendingPropositionCount;
        $this->massUnavailabilitiesCount = $massUnavailabilitiesCount;
        $this->numberOfMeetingsPerPlanning = $numberOfMeetingsPerPlanning;
        $this->numberMaxOfMeetingsPerSheet = $numberMaxOfMeetingsPerSheet;

        $this->slotCount = $slotTotal * $sheetsPlanningQuantity;
        $this->slotsParticipantsCount = $slotTotal * $participantsCount;

        $availableSlotsPerParticipant = $slotTotal - (int) floor($massUnavailabilitiesCount / $participantsCount);

        $minNumberOfMeetingsPerPlanning = $numberOfMeetingsPerPlanning !== null
            ? min($numberOfMeetingsPerPlanning, $availableSlotsPerParticipant)
            : $availableSlotsPerParticipant
        ;

        $maxIntermediateMeetingAvailable = $sheetsPlanningQuantity * $minNumberOfMeetingsPerPlanning;
        $this->maxMeetingAvailable = max(
            0,
            $maxIntermediateMeetingAvailable
        );
        if ($numberMaxOfMeetingsPerSheet) {
            $this->maxMeetingAvailable = min(
                $numberMaxOfMeetingsPerSheet,
                $maxIntermediateMeetingAvailable
            );
        }

        $this->availableSlotsCount = $this->slotsParticipantsCount - $unavailabilitiesCount;
        $this->possibleMeetingsQuantity = max(
            0,
            min($meetingRequestsCount, $this->slotCount, $this->availableSlotsCount, $this->maxMeetingAvailable)
        );
        $this->usableSlots = max(0, min($this->slotCount, $this->availableSlotsCount, $numberMaxOfMeetingsPerSheet ?? +INF));
    }
}
