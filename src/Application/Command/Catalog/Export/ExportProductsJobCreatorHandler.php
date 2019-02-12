<?php

namespace Proximum\Vimeet\Application\Command\Catalog\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class ExportProductsJobCreatorHandler
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
     * @param ExportProductsJobCreator $command
     */
    public function handle(ExportProductsJobCreator $command): void
    {
        $this->jobQueue->exportOrdersForEvent(
            $command->event,
            $command->admin,
            $command->locale
        );
    }
}
