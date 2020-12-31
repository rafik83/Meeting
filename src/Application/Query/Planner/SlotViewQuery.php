<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Domain\Model\Event;

class SlotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Day[]
     */
    public $days;

    /**
     * @param Event $event
     * @param Day[] $days
     */
    public function __construct(Event $event, array $days)
    {
        $this->event = $event;
        $this->days  = $days;
    }
}
