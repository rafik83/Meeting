<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event\Day;

class AgendaDayViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @param Day $day
     */
    public function __construct(Day $day)
    {
        $this->day = $day;
    }
}
