<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class EventOpenAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        return null !== $event->getOpenDate() && $event->getOpenDate() <= $this->dateTime;
    }
}
