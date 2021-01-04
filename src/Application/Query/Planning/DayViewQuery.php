<?php

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;

class DayViewQuery
{
    /** @var Mass[] */
    public $masses;

    /** @var Meeting[] */
    public $meetings;

    /** @var HappeningParticipation[] */
    public $happenings;

    /** @var Unavailability[] */
    public $unavailabilities;

    /** @var MassAssignment[] */
    public $assignments;

    /** @var User */
    public $user;

    /** @var Day */
    public $day;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /**
     * @param Event                    $event
     * @param User                     $user
     * @param Day                      $day
     * @param string                   $locale
     * @param Unavailability[]         $unavailabilities
     * @param HappeningParticipation[] $happenings
     * @param Mass[]                   $masses
     * @param MassAssignment[]         $assignments
     * @param Meeting[]                $meetings
     */
    public function __construct(
        Event $event,
        User $user,
        Day $day,
        $locale,
        array $unavailabilities,
        array $happenings,
        array $masses,
        array $assignments,
        array $meetings
    ) {
        $this->user             = $user;
        $this->day              = $day;
        $this->locale           = $locale;
        $this->unavailabilities = $unavailabilities;
        $this->happenings       = $happenings;
        $this->masses           = $masses;
        $this->assignments      = $assignments;
        $this->meetings         = $meetings;
        $this->event = $event;
    }
}
