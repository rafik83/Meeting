<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;

class SlotViewQuery
{
    /** @var Event */
    public $event;

    /** @var Day */
    public $day;

    /**
     * SlotViewQuery constructor.
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
