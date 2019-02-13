<?php

namespace Proximum\Vimeet\Application\Command\Product\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;

class ExportProductsJobCreatorHandler
{
    /** @var JobQueueInterface  */
    private $jobQueue;
    
    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }
    
    /**
     * @param ExportProductsJobCreator $command
     */
    public function handle(ExportProductsJobCreator $command): void
    {
        $this->jobQueue->exportProductsForEvent(
            $command->event,
            $command->admin,
            $command->locale
        );
    }
}
