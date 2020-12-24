<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class MeetingPublishedAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getSchedulePublishDate()) {
            return false;
        }

        return $this->datetime >= $event->getConfiguration()->getSchedulePublishDate();
    }
}
