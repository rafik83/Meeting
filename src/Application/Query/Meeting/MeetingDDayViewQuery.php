<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingDDayViewQuery
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
     * @param Meeting $meeting
     * @param string  $locale
     */
    public function __construct(Meeting $meeting, string $locale)
    {
        $this->meeting = $meeting;
        $this->locale  = $locale;
    }
}
