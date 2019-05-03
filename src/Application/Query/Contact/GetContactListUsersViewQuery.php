<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class GetContactListUsersViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    public function __construct(Event $event, Participant $participant)
    {
        $this->event = $event;
        $this->participant = $participant;
    }
}
