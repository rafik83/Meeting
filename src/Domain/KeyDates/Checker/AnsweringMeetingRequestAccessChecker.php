<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class AnsweringMeetingRequestAccessChecker extends AccessChecker
{
    /**
     * Check if the date of close answering meeting request is passed or not
     *
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        // if this date is not specified, the meeting request can be modified
        if (null === $event->getConfiguration()->getCloseAnsweringMeetingRequestDate()) {
            return true;
        }

        return $this->dateTime <= $event->getConfiguration()->getCloseAnsweringMeetingRequestDate();
    }
}
