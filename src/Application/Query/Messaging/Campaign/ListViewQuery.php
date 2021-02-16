<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Event;

class ListViewQuery
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
