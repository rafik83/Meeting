<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingUpdateSlotViewQuery implements Query
{
    /** @var Meeting */
    public $meeting;

    /** @var bool */
    public $visio;

    public function __construct(
        Meeting $meeting,
        bool $visio = false
    ) {
        $this->meeting = $meeting;
        $this->visio = $visio;
    }
}
