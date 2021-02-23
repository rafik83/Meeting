<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\LENI\Save;

use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\Save\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\LongJob;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\Save\LeniApiCallCommand;

class LeniApiCallJobQueue extends AbstractJobQueueAdapter implements LeniApiCallJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(ExtraData $extraData)
    {
        // create a low priority job
        $job = new LongJob(LeniApiCallCommand::NAME, [LeniApiCallCommand::EXTRA_DATA_ID=> $extraData->getId()]);
        $this->setJob($job);
    }
}
