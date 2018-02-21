<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\Comexposium;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice\ComexposiumGetRegistrationsCommand;

class ComexposiumJobQueue extends AbstractJobQueueAdapter implements ComexposiumJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRegistrations(Event $event, array $registrationReferences): void
    {
        $job = new Job(ComexposiumGetRegistrationsCommand::NAME,
            [
                $event->getId(),
                implode(',', $registrationReferences),
            ],
            true,
            Job::DEFAULT_QUEUE,
            Job::PRIORITY_LOW
        );

        $this->setJob($job);
    }
}
