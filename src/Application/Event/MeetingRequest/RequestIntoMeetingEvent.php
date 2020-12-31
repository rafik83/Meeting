<?php

namespace Proximum\Vimeet\Application\Event\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class RequestIntoMeetingEvent extends Event
{
    /**
     * @var Sheet[]
     */
    private $sheets;

    /**
     * @param Sheet[] $sheets
     */
    public function __construct(array $sheets)
    {
        $this->sheets = $sheets;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }
}
