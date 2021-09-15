<?php

namespace Proximum\Vimeet\Application\Event\Tip;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\EventDispatcher\Event;

class AssignedEvent extends Event
{
    /** @var EventModel */
    private $event;

    /** @var Tip */
    private $tip;

    /**
     * @param EventModel $event
     * @param Tip        $tip
     */
    public function __construct(EventModel $event, Tip $tip)
    {
        $this->event = $event;
        $this->tip = $tip;
    }

    /**
     * @return EventModel
     */
    public function getEvent(): EventModel
    {
        return $this->event;
    }

    /**
     * @return Tip
     */
    public function getTip(): Tip
    {
        return $this->tip;
    }
}
