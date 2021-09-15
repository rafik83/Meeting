<?php

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
