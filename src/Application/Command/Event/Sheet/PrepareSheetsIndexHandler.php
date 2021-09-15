<?php

namespace Proximum\Vimeet\Application\Command\Event\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class PrepareSheetsIndexHandler
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
     * @param PrepareSheetsIndex $command
     */
    public function handle(PrepareSheetsIndex $command): void
    {
        $this->jobQueue->indexSheetsByEvent($command->event, $command->reset);
    }
}
