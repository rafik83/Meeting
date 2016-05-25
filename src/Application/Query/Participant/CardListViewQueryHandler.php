<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CardListViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var CardViewQueryHandler
     */
    private $cardViewQueryHandler;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param CardViewQueryHandler   $cardViewQueryHandler
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        CardViewQueryHandler $cardViewQueryHandler
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->cardViewQueryHandler   = $cardViewQueryHandler;
    }

    /**
     * @param CardListViewQuery $cardListViewQuery
     *
     * @return CardListView
     */
    public function handle(CardListViewQuery $cardListViewQuery)
    {
        $participants = $cardListViewQuery->sheet->getParticipants();
        $user         = $cardListViewQuery->user;
        $cardListView = new CardListView();

        foreach ($participants as $participant) {
            $editable      = $participant->getUser() === $user || $cardListViewQuery->sheet->getUserParticipant($user)->isOwner();
            $cardViewQuery = new CardViewQuery($participant, $cardListViewQuery->locale, $editable);

            $cardListView->cardViews[$participant->getId()] = $this->cardViewQueryHandler->handle($cardViewQuery);
        }

        return $cardListView;
    }
}
