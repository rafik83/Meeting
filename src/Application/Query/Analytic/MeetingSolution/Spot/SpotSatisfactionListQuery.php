<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SpotSatisfactionListQuery
{
    /** @var Event */
    public $event;

    /** @var MeetingSlot[] */
    public $slots;

    /**
     * @param Event         $event
     * @param MeetingSlot[] $slots
     */
    public function __construct(Event $event, array $slots)
    {
        $this->event = $event;
        $this->slots = $slots;
    }
}
