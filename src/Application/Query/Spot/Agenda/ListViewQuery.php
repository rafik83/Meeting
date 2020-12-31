<?php

namespace Proximum\Vimeet\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Domain\Model\Event;

class ListViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * ListViewQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
