<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class RegistrationCloseDateAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event): bool
    {
        $registrationCloseDate = $event->getConfiguration()->getRegistrationCloseDate();

        if (null !== $registrationCloseDate) {
            return $this->datetime <= $registrationCloseDate;
        }

        return true;
    }
}
