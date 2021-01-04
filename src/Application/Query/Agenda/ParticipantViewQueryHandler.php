<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView[]
     */
    public function handle(ParticipantViewQuery $query): array
    {
        $participants = [];

        foreach ($query->participants as $participant) {
            $participants[] = new ParticipantView(
                $participant->getId(),
                $this->participantInfoGuesser->guessParticipantCompleteName($participant, $query->locale)
            );
        }

        return $participants;
    }
}
