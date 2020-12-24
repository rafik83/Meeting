<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class MeetingRequestAccessChecker extends AccessChecker
{
    /**
     * Check if the date of close meeting request is passed or not
     *
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        // if this date is not specified, the meeting request are not closed
        if (null === $event->getConfiguration()->getCloseMeetingRequestDate()) {
            return true;
        }

        return $this->datetime <= $event->getConfiguration()->getCloseMeetingRequestDate();
    }
}
