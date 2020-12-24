<?php

namespace Proximum\Vimeet\Domain\User\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class FirstParticipantSheetOfUserGetter
{
    public function getFirstParticipantSheet(User $user, array &$sheets): ?Sheet
    {
        $sheet = null;
        $participant = null;

        foreach ($sheets as $userSheet) {
            $userParticipant = $userSheet->getUserParticipant($user);

            // We need to find the oldest participant of the user (the original Participant)
            if ($userParticipant instanceof Participant
                && (!$participant instanceof Participant || $participant->getId() > $userParticipant->getId()))
            {
                $sheet = $userSheet;
                $participant = $userParticipant;
            }
        }

        if ($sheet === null || $participant === null) {
            return null;
        }

        return $sheet;
    }
}
