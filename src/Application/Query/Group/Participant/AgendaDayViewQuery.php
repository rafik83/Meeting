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
     * @var int
     */
    public $dayNumber;

    /**
     * AgendaDayViewQuery constructor.
     *
     * @param Event     $event
     * @param Event\Day $day
     * @param int       $dayNumber
     */
    public function __construct(Event $event, Event\Day $day, $dayNumber)
    {
        $this->event     = $event;
        $this->day       = $day;
        $this->dayNumber = $dayNumber;
    }
}
