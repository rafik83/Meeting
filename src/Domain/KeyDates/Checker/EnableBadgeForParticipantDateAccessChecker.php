<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class EnableBadgeForParticipantDateAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event): bool
    {
        $enableBadgeForParticipantDate = $event->getConfiguration()->getEnableBadgeForParticipantDate();

        if (null !== $enableBadgeForParticipantDate) {
            return $enableBadgeForParticipantDate <= $this->datetime;
        }

        return false;
    }
}
