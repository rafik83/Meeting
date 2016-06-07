<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

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
     * @return ParticipantView
     */
    public function handle(ParticipantViewQuery $participantViewQuery)
    {
        $cardView = $this->cardViewQueryHandler->handle(
            new CardViewQuery(
                $participantViewQuery->participant,
                $participantViewQuery->locale
            )
        );

        return new ParticipantView(
            $participantViewQuery->participant->getId(),
            $cardView,
            $participantViewQuery->participantProduct->getUnitPrice(),
            $participantViewQuery->participant->getSheet()->getEvent()->getMode(),
            $participantViewQuery->included
        );
    }
}
