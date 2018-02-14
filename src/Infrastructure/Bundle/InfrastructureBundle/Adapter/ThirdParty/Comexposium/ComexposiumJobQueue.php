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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice\ComexposiumGetRegistrationCommand;

class ComexposiumJobQueue extends AbstractJobQueueAdapter implements ComexposiumJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRegistration(Event $event, string $registrationReference): void
    {
        $job = new Job(ComexposiumGetRegistrationCommand::NAME,
            [
                $event->getId(),
                $registrationReference,
            ],
            true,
            Job::DEFAULT_QUEUE,
            Job::PRIORITY_LOW
        );

        $this->setJob($job);
    }
}
