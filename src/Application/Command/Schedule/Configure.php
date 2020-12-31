<?php

namespace Proximum\Vimeet\Application\Command\Schedule;

use Proximum\Vimeet\Domain\Model\Event;

class Configure
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var int
     */
    public $scale;

    /**
     * Configure constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->scale = $this->event->getConfiguration()->getScheduleScale();
    }
}
