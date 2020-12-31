<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Adapter;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;
use JMS\JobQueueBundle\Entity\Repository\JobManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Adapter\JobRepositoryAdapterInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\User\Agenda\Version\GenerateVersionsCommand;

class JobRepositoryAdapter implements JobRepositoryAdapterInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @var JobManager */
    private $jobManager;

    /**
     * @param EntityManager $entityManager
     * @param JobManager    $jobManager
     */
    public function __construct(EntityManager $entityManager, JobManager $jobManager)
    {
        $this->entityManager = $entityManager;
        $this->jobManager = $jobManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findGenerateVersionJobForEvent(Event $event): ?Job
    {
        /** @var Job[] $jobs */
        $jobs = $this->jobManager->findAllForRelatedEntity($event);

        foreach ($jobs as $job) {
            if (GenerateVersionsCommand::NAME === $job->getCommand() && !$job->isCanceled()) {
                return $job;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJob(Job $job)
    {
        $this->entityManager->flush($job);
    }
}
