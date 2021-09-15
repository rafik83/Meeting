<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class EmailChecker implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
