<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantFullNameSubstitution implements SubstituteInterface
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet() && !method_exists($prepareMail, 'getParticipant')) {
            return $prepareMail->user->getFullname();
        }

        if (method_exists($prepareMail, 'getParticipant')) {
            $userParticipant = $prepareMail->getParticipant();
        } elseif (property_exists($prepareMail, 'participant')) {
            $userParticipant = $prepareMail->participant;
        } else {
            $userParticipant = $prepareMail->sheet->getUserParticipant($prepareMail->user);
        }

        if (!$userParticipant instanceof Participant) {
            return $prepareMail->user->getFullname();
        }

        return $this->participantInfoGuesser->guessParticipantCompleteName($userParticipant, $prepareMail->locale);
    }
}
