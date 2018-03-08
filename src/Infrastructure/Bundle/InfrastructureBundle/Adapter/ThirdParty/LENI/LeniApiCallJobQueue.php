<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\LENI;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\LENI\LeniApiCallCommand;

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
