<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet;

use Proximum\Vimeet\Domain\Model\Event;

class SheetSatisfactionListQuery
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
