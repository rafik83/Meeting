<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantHelper
{
    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return bool
     */
    public static function isUserAloneParticipant(User $user, Sheet $sheet)
    {
        $participants = $sheet->getParticipants();

        if (1 === count($participants)) {
            $participant = $participants->first();

            if ($participant->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
}
