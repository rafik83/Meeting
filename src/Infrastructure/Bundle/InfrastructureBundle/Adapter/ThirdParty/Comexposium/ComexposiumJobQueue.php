<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\Comexposium;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice\ComexposiumExportSpotCommand;
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

    /**
     * {@inheritdoc}
     */
    public function exportSpot(Event $event, Admin $admin): void
    {
        $job = new Job(ComexposiumExportSpotCommand::NAME, [$event->getId(), $admin->getId()]);
        $this->setJob($job);
    }
}
