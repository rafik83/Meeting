<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event\Day;

class SlotViewsQuery
{
    /** @var Day */
    public $day;

    /**
     * SlotViewsQuery constructor.
     *
     * @param Day $day
     */
    public function __construct(Day $day)
    {
        $this->day = $day;
    }
}
