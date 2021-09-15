<?php

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\ParticipantView;

class ParticipantViewQueryHandler
{
    /**
     * @var CardViewQueryHandler
     */
    private $cardViewQueryHandler;

    /**
     * @param CardViewQueryHandler $cardViewQueryHandler
     */
    public function __construct(CardViewQueryHandler $cardViewQueryHandler)
    {
        $this->cardViewQueryHandler = $cardViewQueryHandler;
    }

    /**
     * @param ParticipantViewQuery $participantViewQuery
     *
     * @return ParticipantView
     */
    public function handle(ParticipantViewQuery $participantViewQuery): ParticipantView
    {
        return new ParticipantView(
            $participantViewQuery->participant->getId(),
            $this->cardViewQueryHandler->handle(
                new CardViewQuery(
                    $participantViewQuery->participant,
                    $participantViewQuery->locale
                )
            )
        );
    }
}
