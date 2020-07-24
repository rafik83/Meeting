<?php

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\AbstractStep;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Exception\StepNotImplementedException;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class StepCommandFactory
{
    /** @var StepPlan */
    private $stepPlan;

    /** @var StepParticipantAndPlanning */
    private $stepParticipantAndPlanning;

    /** @var StepOption */
    private $stepOption;

    public function __construct(
        StepPlan $stepPlan,
        StepParticipantAndPlanning $stepParticipantAndPlanning,
        StepOption $stepOption
    ) {
        $this->stepPlan                   = $stepPlan;
        $this->stepParticipantAndPlanning = $stepParticipantAndPlanning;
        $this->stepOption                 = $stepOption;
    }

    /**
     * @param string $type
     * @param Sheet  $sheet
     * @param int    $stepIndex
     *
     * @throws StepNotImplementedException
     *
     * @return AbstractStep
     */
    public function create($type, Sheet $sheet, $stepIndex): AbstractStep
    {
        switch ($type) {
            case Step::TYPE_PLAN:
                return $this->stepPlan->build($sheet, $stepIndex);
            case Step::TYPE_PARTICIPANT_PLANNING:
                return $this->stepParticipantAndPlanning->build($sheet, $stepIndex);
            case Step::TYPE_OPTIONS:
                return $this->stepOption->build($sheet, $stepIndex);
            default:
                throw new StepNotImplementedException(sprintf('Command Package Step type %s not implemented', $type));
        }
    }
}
