<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class HappeningsAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getHappeningsOpenDate()) {
            return false;
        }

        return $this->dateTime >= $event->getConfiguration()->getHappeningsOpenDate();
    }
}
