<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\Vianeo;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Vianeo\VianeoApiCallJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Vianeo\VianeoApiCallCommand;

class VianeoApiCallJobQueue extends AbstractJobQueueAdapter implements VianeoApiCallJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(Sheet $sheet)
    {
        // create a low priority job
        $job = new Job(VianeoApiCallCommand::NAME, [$sheet->getId()], true, Job::DEFAULT_QUEUE, Job::PRIORITY_LOW);
        $this->setJob($job);
    }
}
