<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Meeting;

use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\ParticipantView;
use Proximum\Vimeet\Domain\Rule\Applyer;

class ParticipantViewQueryHandler
{
    /**
     * @var Applyer
     */
    private $ruleApplyer;

    /**
     * @var CardViewQueryHandler
     */
    private $cardViewQueryHandler;

    /**
     * @param Applyer              $ruleApplyer
     * @param CardViewQueryHandler $cardViewQueryHandler
     */
    public function __construct(Applyer $ruleApplyer, CardViewQueryHandler $cardViewQueryHandler)
    {
        $this->ruleApplyer          = $ruleApplyer;
        $this->cardViewQueryHandler = $cardViewQueryHandler;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView
     */
    public function handle(ParticipantViewQuery $query)
    {
        $card = $this->cardViewQueryHandler->handle(
            new CardViewQuery($query->participant, $query->locale, false)
        );
        $this->ruleApplyer->appluRuleForParticipantCard($card, $query->rules);

        return new ParticipantView($card);
    }
}
