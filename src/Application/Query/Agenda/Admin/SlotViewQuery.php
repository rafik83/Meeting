<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class SlotViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var HappeningParticipation[]
     */
    public $happenings;

    /**
     * @var Unavailability[]
     */
    public $unavailabilities;

    /**
     * @var Mass[]
     */
    public $masses;

    /**
     * @var Meeting[]
     */
    public $meetings;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var MassAssignment[]
     */
    public $massAssignments;

    /**
     * @var Meeting[]
     */
    public $meetingOtherSheets;

    /**
     * SlotViewQuery constructor.
     *
     * @param Event                    $event
     * @param Day                      $day
     * @param Sheet                    $sheet
     * @param Participant              $participant
     * @param HappeningParticipation[] $happenings
     * @param Unavailability[]         $unavailabilities
     * @param Mass[]                   $masses
     * @param Meeting[]                $meetings
     * @param MassAssignment[]         $massAssignments
     * @param Meeting[]                $meetingOtherSheets
     */
    public function __construct(
        Event $event,
        Day $day,
        Sheet $sheet,
        Participant $participant,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $meetings,
        array $massAssignments,
        array $meetingOtherSheets
    ) {
        $this->day              = $day;
        $this->event            = $event;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
        $this->meetings         = $meetings;
        $this->sheet            = $sheet;
        $this->participant      = $participant;
        $this->massAssignments  = $massAssignments;
        $this->meetingOtherSheets = $meetingOtherSheets;
    }
}
