<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class LockMeetingRequestUpdate implements Command
{
    /**
     * @var bool
     */
    public $lock;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event $event
     * @param bool  $lock
     */
    public function __construct(Event $event, $lock)
    {
        $this->event = $event;
        $this->lock  = $lock;
    }
}
