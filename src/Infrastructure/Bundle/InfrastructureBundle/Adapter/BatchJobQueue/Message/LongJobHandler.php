<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class LongJobHandler implements MessageHandlerInterface
{
    private RunJob $runJob;

    public function __construct(RunJob $runJob)
    {
        $this->runJob = $runJob;
    }

    function __invoke(LongJob $job)
    {
        $this->runJob->run($job);
    }
}
