<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Application\Command\Package\Step\AbstractStep;
use Proximum\Vimeet\Application\Components\Step\StepOption;
use Proximum\Vimeet\Application\Components\Step\StepParticipantAndPlanning;
use Proximum\Vimeet\Application\Components\Step\StepPlan;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class StepCommandFactory
{
    /**
     * @var StepPlan
     */
    private $stepPlan;

    /**
     * @var StepParticipantAndPlanning
     */
    private $stepParticipantAndPlanning;

    /**
     * @var StepOption
     */
    private $stepOption;

    /**
     * StepCommandFactory constructor.
     *
     * @param StepPlan                   $stepPlan
     * @param StepParticipantAndPlanning $stepParticipantAndPlanning
     * @param StepOption                 $stepOption
     *
     */
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
     * @return AbstractStep
     * @throws \Exception
     */
    public function create($type, Sheet $sheet, $stepIndex)
    {
        switch ($type) {
            case Step::TYPE_PLAN:
                return $this->stepPlan->build($sheet, $stepIndex);
                break;
            case Step::TYPE_PARTICIPANT_PLANNING:
                return $this->stepParticipantAndPlanning->build($sheet, $stepIndex);
                break;
            case Step::TYPE_OPTIONS:
                return $this->stepOption->build($sheet, $stepIndex);
                break;
            default:
                throw new \Exception(sprintf('Command Package Step type %s not implemented', $type));
                break;
        }
    }
}
