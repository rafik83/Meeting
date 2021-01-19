<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;

class SpotFillingRateQuery
{
    /** @var MeetingSlot[] */
    public $meetingSlots;

    /** @var Spot[] */
    public $spots;

    /** @var Event */
    public $event;

    /**
     * @param Event         $event
     * @param MeetingSlot[] $meetingSlots
     * @param Spot[]        $spots
     */
    public function __construct(Event $event, array $meetingSlots, array $spots)
    {
        $this->meetingSlots = $meetingSlots;
        $this->spots = $spots;
        $this->event = $event;
    }
}
