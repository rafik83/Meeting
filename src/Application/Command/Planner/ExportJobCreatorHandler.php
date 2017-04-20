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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ExportPlannerCommand;

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
        $lockMeetingRequest = ExportPlannerCommand::DONT_LOCK_MEETING_REQUEST;

        if ($exportJobCreator->lockMeetingRequest === true) {
            $lockMeetingRequest = ExportPlannerCommand::LOCK_MEETING_REQUEST;
        }

        $this->jobQueue->exportPlannerForEvent(
            $exportJobCreator->event,
            $exportJobCreator->admin,
            $exportJobCreator->locale,
            $lockMeetingRequest,
            $exportJobCreator->solutionType
        );
    }
}
