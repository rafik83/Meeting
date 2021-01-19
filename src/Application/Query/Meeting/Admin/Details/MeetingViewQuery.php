<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\Details;

use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingViewQuery
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
    public function __construct(Meeting $meeting, $locale)
    {
        $this->meeting = $meeting;
        $this->locale  = $locale;
    }
}
