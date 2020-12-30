<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class CancelAttendanceUnavailabilityViewQuery
{
    /** @var Event */
    public $event;

    /** @var TimeRangeInterface */
    public $day;

    public function __construct(Event $event, TimeRangeInterface $day)
    {
        $this->event = $event;
        $this->day = $day;
    }
}
