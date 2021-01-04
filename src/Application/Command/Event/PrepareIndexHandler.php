<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

/**
 * This method reset elasticsearch and re-indexes all the event from scratch
 */
class PrepareIndexHandler
{
    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function handle(PrepareIndex $command): void
    {
        $this->jobQueue->indexEventFromScratch();
    }
}
