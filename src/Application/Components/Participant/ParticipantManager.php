<?php

namespace Proximum\Vimeet\Application\Components\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantManager
{
    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getRemainingPossibleParticipant(Sheet $sheet)
    {
        $max   = $sheet->getType()->getMaxParticipant();
        $added = count($sheet->getParticipants());

        return $max - $added;
    }

    /**
     * A user is allowed to edit a participant if he is the sheet owner or if he is the participant himself
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param User        $user
     *
     * @return bool
     */
    public function isUserAllowedToEditParticipant(Sheet $sheet, Participant $participant, User $user)
    {
        return $sheet->hasParticipant($participant)
            && ($sheet->getOwner() === $user || $participant->getUser() === $user);
    }

    /**
     * A user is allowed to delete a participant if he is the sheet owner and the participant is not the sheet owner
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param User        $user
     *
     * @return bool
     */
    public function isUserAllowedToDeleteParticipant(Sheet $sheet, Participant $participant, User $user)
    {
        return $sheet->hasParticipant($participant)
            && $participant->getUser() !== $user
            && $sheet->getOwner() !== $user;
    }
}
