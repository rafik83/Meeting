<?php

namespace Proximum\Vimeet\Application\Query\MeetingSlot;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class GetAvailableSlotsQuery implements Query
{
    public Meeting $meeting;
    public bool $visio;
    public Sheet $sheet;

    public function __construct(Meeting $meeting, bool $visio = false, Sheet $sheet)
    {
        $this->meeting = $meeting;
        $this->visio = $visio;
        $this->sheet = $sheet;
    }
}
