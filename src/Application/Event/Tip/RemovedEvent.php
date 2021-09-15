<?php

namespace Proximum\Vimeet\Application\Event\Tip;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Symfony\Component\EventDispatcher\Event;

class RemovedEvent extends Event
{
    /** @var EventModel */
    private $event;

    /**
     * @param EventModel $event
     */
    public function __construct(EventModel $event)
    {
        $this->event = $event;
    }

    /**
     * @return EventModel
     */
    public function getEvent(): EventModel
    {
        return $this->event;
    }
}
