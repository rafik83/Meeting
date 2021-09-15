<?php

namespace Proximum\Vimeet\Application\Event\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\EventDispatcher;

class KeyDatesUpdatedEvent extends EventDispatcher\Event
{
    /** @var Event */
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
    public function getEvent(): Event
    {
        return $this->event;
    }
}
