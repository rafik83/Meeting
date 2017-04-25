<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;

class SlotViewsQuery
{
    /** @var Event */
    public $event;

    /** @var Day */
    public $day;

    /**
     * SlotViewsQuery constructor.
     *
     * @param Event $event
     * @param Day   $day
     */
    public function __construct(Event $event, Day $day)
    {
        $this->event = $event;
        $this->day   = $day;
    }
}
