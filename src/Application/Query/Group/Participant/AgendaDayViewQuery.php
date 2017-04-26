<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;

class AgendaDayViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Event\Day
     */
    public $day;

    /**
     * AgendaDayViewQuery constructor.
     *
     * @param Event     $event
     * @param Event\Day $day
     */
    public function __construct(Event $event, Event\Day $day)
    {
        $this->event     = $event;
        $this->day       = $day;
    }
}
