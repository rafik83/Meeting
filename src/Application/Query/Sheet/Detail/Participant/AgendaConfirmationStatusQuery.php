<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class AgendaConfirmationStatusQuery
{
    /** @var Participant */
    public $participant;

    /** @var Event */
    public $event;

    /**
     * AgendaConfirmationStatusQuery constructor.
     *
     * @param Participant $participant
     * @param Event       $event
     */
    public function __construct(Participant $participant, Event $event)
    {
        $this->participant = $participant;
        $this->event = $event;
    }
}
