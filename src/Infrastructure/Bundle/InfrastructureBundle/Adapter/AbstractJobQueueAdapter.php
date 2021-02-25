<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\DBAL\Types\Types;
use LogicException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractJobQueueAdapter
{
    private MessageBusInterface $bus;
    private LockFactory $jobLockFactory;
    private ?LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $bus,
        LockFactory $jobLockFactory,
        LoggerInterface $logger = null
    ) {
        $this->bus = $bus;
        $this->jobLockFactory = $jobLockFactory;
        $this->logger = $logger;
    }

    protected function setJob(Job $job): void
    {
        // lock will be released in job handler
        $lock = $this->jobLockFactory->createLock($job->getLockKey(), $job->getMaxExecutionTime(), false);

        // Avoid to set a job when the same job is already pending.
        if (!$lock->acquire()) {
            if ($this->logger) {
                $this->logger->warning(
                    '{command}: Job is already running, cancel message dispatch',
                    ['command' => $job->getLockKey(), 'args' => $job->getArgs()]
                );
            }

            return;
        }

        $this->bus->dispatch($job);
    }

    protected function removeJob(Job $job): void
    {
        throw new LogicException('Deprecated');

        //  $this->entityManager
        //      ->createQueryBuilder()
        //      ->delete()
        //      ->from(Job::class, 'job')
        //      ->where('job.command = :command')
        //      ->andWhere('job.args = :args')
        //      ->andWhere('job.state = :state')
        //     ->setParameter('command', $command)
        //     ->setParameter('args', $args, Types::JSON)
        //     ->setParameter('state', Job::STATE_PENDING)
        //     ->getQuery()
        //     ->execute()
        // ;
    }
}
