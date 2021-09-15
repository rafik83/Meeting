<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;

class FillingRateQuery
{
    /** @var MeetingSlot[] */
    public $slots;

    /** @var Spot[] */
    public $spots;

    /** @var Event */
    public $event;

    /**
     * @param Event $event
     * @param array $slots
     * @param array $spots
     */
    public function __construct(Event $event, array $slots, array $spots)
    {
        $this->slots = $slots;
        $this->spots = $spots;
        $this->event = $event;
    }
}
