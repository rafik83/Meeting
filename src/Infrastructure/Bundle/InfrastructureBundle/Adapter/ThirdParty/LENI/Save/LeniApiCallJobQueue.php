<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\LENI\Save;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\Save\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Save\LeniApiCallCommand;

class LeniApiCallJobQueue extends AbstractJobQueueAdapter implements LeniApiCallJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(ExtraData $extraData)
    {
        // create a low priority job
        $job = new Job(LeniApiCallCommand::NAME, [$extraData->getId()], true, Job::DEFAULT_QUEUE, Job::PRIORITY_LOW);
        $this->setJob($job);
    }
}
