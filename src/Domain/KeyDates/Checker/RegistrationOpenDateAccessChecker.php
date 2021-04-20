<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class RegistrationOpenDateAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event): bool
    {
        $registrationOpenDate = $event->getConfiguration()->getRegistrationOpenDate();

        if (null !== $registrationOpenDate) {
            return $this->dateTime >= $registrationOpenDate;
        }

        return true;
    }
}
