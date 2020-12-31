<?php

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class AvailableSlotsByParticipantQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /**
     * @param Event       $event
     * @param Participant $participant
     */
    public function __construct(Event $event, Participant $participant)
    {
        $this->event = $event;
        $this->participant = $participant;
    }
}
