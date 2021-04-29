<?php


namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class NetworkingAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getNetworkingOpenDate() && null === $event->getConfiguration()->getNetworkingCloseDate()) {
            return false;
        }

        return $this->dateTime <= $event->getConfiguration()->getNetworkingCloseDate() && $this->dateTime >= $event->getConfiguration()->getNetworkingOpenDate();
    }
}
