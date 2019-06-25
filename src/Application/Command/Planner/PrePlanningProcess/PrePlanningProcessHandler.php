<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;

class PrePlanningProcessHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(PrePlanningProcess $prePlanningProcess): void
    {
        $this->commandBus->handle(
            new TransformPriorityRequestsIntoMeeting(
                $prePlanningProcess->event,
                $prePlanningProcess->solutionType
            )
        );

        $this->commandBus->handle(
            new TransformApprovedRequestsByLinkedSheetsIntoMeeting(
                $prePlanningProcess->event,
                $prePlanningProcess->solutionType
            )
        );
    }
}
