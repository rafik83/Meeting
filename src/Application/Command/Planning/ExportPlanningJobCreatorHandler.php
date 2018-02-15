<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class ExportPlanningJobCreatorHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param JobQueueInterface $jobQueue
     */
    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param ExportPlanningJobCreator $exportPlanning
     */
    public function handle(ExportPlanningJobCreator $exportPlanning)
    {
        $this->jobQueue->printPlanning(
            $exportPlanning->types,
            $exportPlanning->orderBy,
            $exportPlanning->emailToNotify,
            $exportPlanning->locale
        );
    }
}
