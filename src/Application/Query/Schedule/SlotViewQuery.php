<?php

namespace Proximum\Vimeet\Application\Query\Schedule;

use Proximum\Vimeet\Domain\Model\Event;

class SlotViewQuery
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
