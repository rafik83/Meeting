<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant $participant */
    public $participant;

    /** @var Event */
    public $event;

    /** @var Day[] */
    public $eventDays;

    /**
     * ParticipantViewQuery constructor.
     *
     * @param Participant $participant
     * @param Event       $event
     * @param Day[]       $eventDays
     */
    public function __construct(Participant $participant, Event $event, array $eventDays)
    {
        $this->participant = $participant;
        $this->event       = $event;
        $this->eventDays   = $eventDays;
    }
}
