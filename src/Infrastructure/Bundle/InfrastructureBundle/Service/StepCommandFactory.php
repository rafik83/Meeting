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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StepCommandFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * StepCommandFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
                return $this->container->get('components.step.step_plan')->build($sheet, $stepIndex);
                break;
            case Step::TYPE_PARTICIPANT_PLANNING:
                return $this->container->get('components.step.step_participant')->build($sheet, $stepIndex);
                break;
            case Step::TYPE_OPTIONS:
                return $this->container->get('components.step.step_option')->build($sheet, $stepIndex);
                break;
            default:
                throw new \Exception(sprintf('Command Package Step type %s not implemented', $type));
                break;
        }
    }
}
