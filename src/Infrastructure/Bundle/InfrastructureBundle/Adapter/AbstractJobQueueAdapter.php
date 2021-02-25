<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\DBAL\Types\Types;
use LogicException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractJobQueueAdapter
{
    private MessageBusInterface $bus;

    public function __construct(
        MessageBusInterface $bus
    ) {
        $this->bus = $bus;
    }

    protected function setJob(Job $job): void
    {
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
