<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;

abstract class AbstractJobQueueAdapter
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function hasAlreadyJobPending(string $command, array $args): bool
    {
        $pendingJob = $this->entityManager
            ->createQuery("SELECT j FROM JMSJobQueueBundle:Job j
                WHERE j.command = :command
                AND j.args = :args
                AND j.state = :state
            ")
            ->setParameter('command', $command)
            ->setParameter('args', $args, Types::JSON)
            ->setParameter('state', Job::STATE_PENDING)
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        return null !== $pendingJob;
    }

    protected function setJob(Job $job): void
    {
        $command = $job->getCommand();
        $args = $job->getArgs();

        // Avoid to set a job when the same job is already pending.
        if ($this->hasAlreadyJobPending($command, $args)) {
            return;
        }

        $this->entityManager->persist($job);
        $this->entityManager->flush($job);
    }

    protected function updateJob(Job $job): void
    {
        $this->entityManager->flush($job);
    }

    protected function removeJob(string $command, array $args): void
    {
         $this->entityManager
             ->createQueryBuilder()
             ->delete()
             ->from(Job::class, 'job')
             ->where('job.command = :command')
             ->andWhere('job.args = :args')
             ->andWhere('job.state = :state')
            ->setParameter('command', $command)
            ->setParameter('args', $args, Types::JSON)
            ->setParameter('state', Job::STATE_PENDING)
            ->getQuery()
            ->execute()
        ;
    }
}
