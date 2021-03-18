<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class UserFirstNameSubstitution implements SubstituteInterface
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return $prepareMail->user->getFirstName();
        }

        $userParticipant = $prepareMail->sheet->getUserParticipant($prepareMail->user);

        if (!$userParticipant instanceof Participant) {
            return $prepareMail->user->getFirstName();
        }

        return $this->participantInfoGuesser->guessParticipantFirstName($userParticipant, $prepareMail->locale);
    }
}
