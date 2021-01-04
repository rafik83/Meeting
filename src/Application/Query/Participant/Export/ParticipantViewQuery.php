<?php

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    public function __construct(Event $event, Participant $participant, string $locale)
    {
        $this->participant = $participant;
        $this->locale = $locale;
        $this->event = $event;
    }
}
