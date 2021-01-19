<?php

namespace Proximum\Vimeet\Application\View\Planning;

use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;
use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;
use Proximum\Vimeet\Application\View\Planning\Day\MassView;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;

class DayView
{
    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var HappeningParticipationView[] */
    public $happenings;

    /** @var UnavailabilityView[] */
    public $unavailabilities;

    /** @var MassView[] */
    public $masses;

    /** @var AssignmentView[] */
    public $assignments;

    /** @var MeetingView[] */
    public $meetings;

    /**
     * @param \DateTimeInterface           $begin
     * @param \DateTimeInterface           $end
     * @param HappeningParticipationView[] $happenings
     * @param UnavailabilityView[]         $unavailabilities
     * @param MassView[]                   $masses
     * @param AssignmentView[]             $assignments
     * @param MeetingView[]                $meetings
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        array $happenings,
        array $unavailabilities,
        array $masses,
        array $assignments,
        array $meetings
    ) {
        $this->begin            = $begin;
        $this->end              = $end;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
        $this->masses           = $masses;
        $this->assignments      = $assignments;
        $this->meetings         = $meetings;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay()
    {
        return $this->begin;
    }

    /**
     * @return array
     */
    public function getTimeEntities()
    {
        return array_merge(
            $this->happenings,
            $this->unavailabilities,
            $this->masses,
            $this->assignments,
            $this->meetings
        );
    }
}
