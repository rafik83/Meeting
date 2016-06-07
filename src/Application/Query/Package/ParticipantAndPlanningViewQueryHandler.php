<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Application\View\Package\ParticipantAndPlanningView;

class ParticipantAndPlanningViewQueryHandler
{
    /**
     * @var ParticipantsViewQueryHandler
     */
    private $participantsViewQueryHandler;

    /**
     * @var PlanningViewQueryHandler
     */
    private $planningViewQueryHandler;

    /**
     * @param ParticipantsViewQueryHandler $participantsViewQueryHandler
     * @param PlanningViewQueryHandler $planningViewQueryHandler
     */
    public function __construct(
        ParticipantsViewQueryHandler $participantsViewQueryHandler,
        PlanningViewQueryHandler $planningViewQueryHandler
    ) {
        $this->participantsViewQueryHandler = $participantsViewQueryHandler;
        $this->planningViewQueryHandler     = $planningViewQueryHandler;
    }

    public function handle(ParticipantAndPlanningViewQuery $participantAndPlanningViewQuery)
    {
        $participantsView = $this->participantsViewQueryHandler->handle(
            new ParticipantsViewQuery(
                $participantAndPlanningViewQuery->sheet,
                $participantAndPlanningViewQuery->locale
            )
        );
        $planningView = $this->planningViewQueryHandler->handle(

        )

        $participantAndPlanningView = new ParticipantAndPlanningView();

        return $participantAndPlanningView;
    }
}
