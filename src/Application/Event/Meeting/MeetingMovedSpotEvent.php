<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\EventDispatcher\Event;

class MeetingMovedSpotEvent extends Event
{
    /** @var Meeting */
    private $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }
}
