<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant $participant */
    public $participant;

    /** @var Event */
    public $event;

    /**
     * ParticipantViewQuery constructor.
     *
     * @param Participant $participant
     */
    public function __construct(Participant $participant, Event $event)
    {
        $this->participant = $participant;
        $this->event       = $event;
    }
}
