<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\ParticipantView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ParticipantViewQueryHandler constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView
     */
    public function handle(ParticipantViewQuery $query)
    {
        $firstName = $this
            ->participantInfoGuesser
            ->guessParticipantFirstName($query->participant, $query->participant->getLocale());

        $lastName = $this
            ->participantInfoGuesser
            ->guessParticipantLastName($query->participant, $query->participant->getLocale());

        return new ParticipantView($firstName, $lastName, $firstName . ' '  . $lastName);
    }
}
