<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class MeetingMovedEvent extends Event
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

    /**
     * @return Sheet[]
     */
    public function getSheets(): array
    {
        return [$this->meeting->getFromSheet(), $this->meeting->getToSheet()];
    }
}
