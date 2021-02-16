<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class MeetingSolutionListQuery implements Query
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
