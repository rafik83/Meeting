<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event\Day;

class DayViewQuery
{
    /** @var Day[] */
    public $days;

    /**
     * @param Day[] $days
     */
    public function __construct(array $days = [])
    {
        $this->days = $days;
    }
}
