<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Message;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

final class ListQuery implements Query
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
