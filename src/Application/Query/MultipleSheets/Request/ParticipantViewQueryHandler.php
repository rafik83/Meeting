<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\ParticipantView;

class ParticipantViewQueryHandler
{
    /** @var ParticipantInfoGuesserCache */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesserCache $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesserCache $participantInfoGuesser)
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
        return new ParticipantView(
            $this->participantInfoGuesser->guessParticipantCompleteName($query->participant, $query->locale)
        );
    }
}
