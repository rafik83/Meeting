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

class ExportJobCreatorHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * ExportJobCreatorHandler constructor.
     *
     * @param JobQueueInterface $jobQueue
     */
    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param ExportJobCreator $exportJobCreator
     */
    public function handle(ExportJobCreator $exportJobCreator)
    {
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
