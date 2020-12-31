<?php

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantsViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Package\Planning\PlanningViewQueryHandler;
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
     * @param PlanningViewQueryHandler     $planningViewQueryHandler
     */
    public function __construct(
        ParticipantsViewQueryHandler $participantsViewQueryHandler,
        PlanningViewQueryHandler $planningViewQueryHandler
    ) {
        $this->participantsViewQueryHandler = $participantsViewQueryHandler;
        $this->planningViewQueryHandler     = $planningViewQueryHandler;
    }

    /**
     * @param ParticipantAndPlanningViewQuery $participantAndPlanningViewQuery
     *
     * @return ParticipantAndPlanningView
     */
    public function handle(ParticipantAndPlanningViewQuery $participantAndPlanningViewQuery)
    {
        $participantsView = $this->participantsViewQueryHandler->handle(
            new ParticipantsViewQuery(
                $participantAndPlanningViewQuery->sheet,
                $participantAndPlanningViewQuery->locale
            )
        );

        $planningView = $this->planningViewQueryHandler->handle(
            new PlanningViewQuery(
                $participantAndPlanningViewQuery->sheet,
                $participantAndPlanningViewQuery->sheet->getPackage()->getParticipant(),
                $participantAndPlanningViewQuery->locale
            )
        );

        $participantAndPlanningView = new ParticipantAndPlanningView($participantsView, $planningView);

        return $participantAndPlanningView;
    }
}
