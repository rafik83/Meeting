<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class AgendaDayViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var int
     */
    public $dayNumber;

    /**
     * @var string
     */
    public $locale;

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
     * @param Sheet                    $sheet
     * @param Day                      $day
     * @param int                      $dayNumber
     * @param Participant              $participant
     * @param string                   $locale
     * @param HappeningParticipation[] $happenings
     * @param Unavailability[]         $unavailabilities
     * @param Mass[]                   $masses
     * @param Meeting[]                $meetings
     * @param MassAssignment[]         $massAssigments
     * @param Meeting[]                $meetingOtherSheets
     */
    public function __construct(
        Sheet $sheet,
        Day $day,
        $dayNumber,
        Participant $participant,
        $locale,
        array $happenings = [],
        array $unavailabilities = [],
        array $masses = [],
        array $meetings = [],
        array $massAssigments = [],
        array $meetingOtherSheets = []
    ) {
        $this->day              = $day;
        $this->dayNumber        = $dayNumber;
        $this->locale           = $locale;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
        $this->meetings         = $meetings;
        $this->sheet            = $sheet;
        $this->participant      = $participant;
        $this->massAssignments  = $massAssigments;
        $this->meetingOtherSheets = $meetingOtherSheets;
    }
}
