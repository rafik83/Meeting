<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsViewQuery
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Day[] */
    public $eventDays;

    /**
     * @param Sheet[] $sheets
     * @param Day[]   $eventDays
     */
    public function __construct($sheets, $eventDays)
    {
        $this->sheets    = $sheets;
        $this->eventDays = $eventDays;
    }
}
