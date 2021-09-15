<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class UnavailabilityViewQuery
{
    /** @var Unavailability */
    public $unavailability;

    /** @var Event */
    public $event;

    /** @var TimeRangeView */
    public $day;

    public function __construct(Unavailability $unavailability, Event $event, TimeRangeView $day)
    {
        $this->unavailability = $unavailability;
        $this->event = $event;
        $this->day = $day;
    }
}
