<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class ExportJobCreatorHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param JobQueueInterface             $jobQueue
     * @param PlannerJobRepositoryInterface $plannerJobRepository
     * @param \DateTimeInterface            $dateTime
     */
    public function __construct(
        JobQueueInterface $jobQueue,
        PlannerJobRepositoryInterface $plannerJobRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->jobQueue             = $jobQueue;
        $this->plannerJobRepository = $plannerJobRepository;
        $this->dateTime             = $dateTime;
    }

    /**
     * @param ExportJobCreator $exportJobCreator
     */
    public function handle(ExportJobCreator $exportJobCreator)
    {
        if ($exportJobCreator->isModeAuto()) {
            $plannerJob = new PlannerJob(
                $exportJobCreator->event,
                $exportJobCreator->admin,
                $exportJobCreator->solutionType,
                $exportJobCreator->lockMeetingRequest,
                $this->dateTime
            );
            $this->plannerJobRepository->add($plannerJob);
        }

        $this->jobQueue->exportPlannerForEvent(
            $exportJobCreator->event,
            $exportJobCreator->admin,
            $exportJobCreator->locale,
            $exportJobCreator->lockMeetingRequest,
            $exportJobCreator->solutionType,
            $exportJobCreator->isModeAuto()
        );
    }
}
