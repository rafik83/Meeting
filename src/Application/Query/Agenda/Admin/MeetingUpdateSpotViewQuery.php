<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingUpdateSpotViewQuery
{
    /** @var Meeting */
    public $meeting;

    /** @var bool */
    public $visio;

    /**
     * @param Meeting $meeting
     * @param bool    $visio
     */
    public function __construct(Meeting $meeting, $visio = false)
    {
        $this->meeting = $meeting;
        $this->visio   = $visio;
    }
}
