<?php

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class MeetingAccess
{
    /**
     * @param Meeting $meeting
     * @param User    $user
     *
     * @return bool
     */
    public function allowedToAccess(Meeting $meeting, User $user): bool
    {
        foreach ($meeting->getAllParticipants() as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
