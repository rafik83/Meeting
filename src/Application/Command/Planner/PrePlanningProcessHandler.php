<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class PrePlanningProcessHandler
{
    public function handle(PrePlanningProcess $prePlanningProcess): void
    {
        $this->setMeetingAcceptedByLinkedSheets($prePlanningProcess);
    }

    private function setMeetingAcceptedByLinkedSheets(PrePlanningProcess $prePlanningProcess): void
    {
        if (ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED !== $prePlanningProcess->solutionType) {
            return;
        }

        // @todo
        // 1. get linked sheets
    }
}
