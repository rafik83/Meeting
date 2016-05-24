<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;


use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CardListViewQueryHandler
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
            $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $cardListViewQuery->locale);

            $editable = $participant->getUser() === $user || $cardListViewQuery->sheet->getUserParticipant($user)->isOwner();

            $cardView = new CardView(
                $participant->getId(),
                $editable,
                $infos[Tag::PARTICIPANT_FIRSTNAME],
                $infos[Tag::PARTICIPANT_LASTNAME],
                $infos[Tag::PARTICIPANT_POSITION],
                $infos[Tag::PARTICIPANT_AVATAR],
                $participant->isOwner()
            );

            $cardListView->cardViews[$participant->getId()] = $cardView;
        }

        return $cardListView;
    }
}
