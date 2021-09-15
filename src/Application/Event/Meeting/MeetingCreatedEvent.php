<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\EventDispatcher\Event;

class MeetingCreatedEvent extends Event
{
    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * @return Meeting
     */
    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }
}
