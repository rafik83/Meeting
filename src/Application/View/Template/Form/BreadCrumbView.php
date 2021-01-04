<?php

namespace Proximum\Vimeet\Application\View\Template\Form;

class BreadCrumbView
{
    /** @var StepView[] */
    public $steps;

    /** @var int */
    public $currentStepIndex;

    /**
     * @param StepView[] $steps indexed by stepIndex
     * @param int        $currentStepIndex
     */
    public function __construct(array $steps, int $currentStepIndex)
    {
        $this->steps = $steps;
        $this->currentStepIndex = $currentStepIndex;
    }

    public function getTotalNumberOfStep(): int
    {
        return \count($this->steps);
    }

    public function getCurrentStep(): StepView
    {
        return $this->steps[$this->currentStepIndex];
    }

    public function getNextStep(): ?StepView
    {
        return $this->steps[$this->currentStepIndex + 1] ?? null;
    }
}
