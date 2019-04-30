<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class GetContactListViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Participant $participant, string $locale)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->locale = $locale;
    }
}
