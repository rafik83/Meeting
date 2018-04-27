<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\View\Participant\CardListView;

class CardListViewQueryHandler
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
            if ($cardListViewQuery->editable) {
                $editable = $participant->getUser() === $user || $cardListViewQuery->sheet->isOwner($user);
            } else {
                $editable = false;
            }

            $cardViewQuery = new CardViewQuery($participant, $cardListViewQuery->locale, $editable);

            $cardListView->cardViews[$participant->getId()] = $this->cardViewQueryHandler->handle($cardViewQuery);
        }

        return $cardListView;
    }
}
