<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class ScheduleExportHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function handle(ScheduleExport $command): void
    {
        $this->jobQueue->exportOmzUser($command->event, $command->admin);
    }
}
