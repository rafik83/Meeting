<?php

namespace Proximum\Vimeet\Application\Query\Schedule;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class SlotViewQuery implements Query
{
    /**
     * @var Event
     */
    public $event;

    /**
     * SlotQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
