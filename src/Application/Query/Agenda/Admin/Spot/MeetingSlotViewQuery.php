<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingSlotViewQuery
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var string
     */
    public $locale;

    /**
     * MeetingSlotViewQuery constructor.
     *
     * @param Meeting $meeting
     * @param string  $locale
     */
    public function __construct(Meeting $meeting, $locale)
    {
        $this->meeting = $meeting;
        $this->locale  = $locale;
    }
}
