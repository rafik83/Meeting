<?php

namespace Proximum\Vimeet\Application\Command\Order\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class ExportJobCreatorHandler
{
    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param JobQueueInterface $jobQueue
     */
    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param ExportJobCreator $command
     */
    public function handle(ExportJobCreator $command)
    {
        $this->jobQueue->exportOrdersForEvent(
            $command->event,
            $command->admin,
            $command->locale
        );
    }
}
