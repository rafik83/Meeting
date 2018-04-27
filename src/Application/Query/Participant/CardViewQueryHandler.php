<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CardViewQueryHandler
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
     * @param CardViewQuery $cardViewQuery
     *
     * @return CardView
     */
    public function handle(CardViewQuery $cardViewQuery)
    {
        $infos = $this->participantInfoGuesser->guessParticipantInfos($cardViewQuery->participant, $cardViewQuery->locale);

        $cardView = new CardView(
            $cardViewQuery->participant->getId(),
            $cardViewQuery->editable,
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR],
            $cardViewQuery->participant->isOwnerParticipant(),
            $cardViewQuery->participant->getSheet()->getId()
        );

        return $cardView;
    }
}
