<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingUpdateSpotViewQuery
{
    /** @var Meeting */
    public $meeting;

    /** @var bool */
    public $visio;

    public Sheet $sheet;

    /**
     * @param Meeting $meeting
     * @param bool    $visio
     */
    public function __construct(Meeting $meeting, Sheet $sheet, $visio = false)
    {
        $this->meeting = $meeting;
        $this->sheet = $sheet;
        $this->visio   = $visio;
    }
}
