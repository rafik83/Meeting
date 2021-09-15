<?php

namespace Proximum\Vimeet\Application\Query\Participant\Sheet;

use Proximum\Vimeet\Application\View\Participant\Sheet\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Sheet\ParticipantView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantListViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function handle(ParticipantListViewQuery $query): ParticipantListView
    {
        $participants = [];
        $currentParticipant = null;

        foreach ($query->sheet->getParticipantsArray() as $participant) {
            $participantView = new ParticipantView(
                $participant->getId(),
                $this->participantInfoGuesser->guessParticipantCompleteName($participant, $query->locale)
            );

            if ($participant->getId() === $query->currentParticipant->getId()) {
                $currentParticipant = $participantView;
            }

            $participants[] = $participantView;
        }

        return new ParticipantListView($currentParticipant, $participants);
    }
}
