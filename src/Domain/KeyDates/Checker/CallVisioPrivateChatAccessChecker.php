<?php


namespace Proximum\Vimeet\Domain\KeyDates\Checker;


use Proximum\Vimeet\Domain\Model\Event;

class CallVisioPrivateChatAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getCallVisioOpenDate() && null === $event->getConfiguration()->getCallVisioCloseDate()) {
            return false;
        }

        return $this->datetime <= $event->getConfiguration()->getCallVisioCloseDate() && $this->datetime >= $event->getConfiguration()->getCallVisioOpenDate();
    }
}
