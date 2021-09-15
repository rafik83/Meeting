<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class AgendaParticipantViewQuery
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var HappeningParticipation[]
     */
    public $happeningParticipations;

    /**
     * @var Unavailability[]
     */
    public $unavailabilities;

    /**
     * @var Unavailability\Mass[]
     */
    public $masses;

    /**
     * @var Meeting[]
     */
    public $meetings;

    /**
     * @var MassAssignment[]
     */
    public $massAssignments;

    /**
     * AgendaParticipantViewQuery constructor.
     *
     * @param Participant              $participant
     * @param Event                    $event
     * @param Sheet                    $sheet
     * @param string                   $locale
     * @param HappeningParticipation[] $happeningParticipations
     * @param Unavailability[]         $unavailabilities
     * @param Unavailability\Mass[]    $masses
     * @param Meeting[]                $meetings
     * @param MassAssignment[]         $assignment
     */
    public function __construct(
        Participant $participant,
        Event $event,
        Sheet $sheet,
        $locale,
        array $happeningParticipations,
        array $unavailabilities,
        array $masses,
        array $meetings,
        array $assignment
    ) {
        $this->event                   = $event;
        $this->participant             = $participant;
        $this->sheet                   = $sheet;
        $this->locale                  = $locale;
        $this->happeningParticipations = $happeningParticipations;
        $this->unavailabilities        = $unavailabilities;
        $this->masses                  = $masses;
        $this->meetings                = $meetings;
        $this->massAssignments         = $assignment;
    }
}
