<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

abstract class AbstractJobQueueAdapter
{
    private MessageBusInterface $bus;
    private CrossProcessLockFactory $jobLockFactory;
    private ?LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $bus,
        CrossProcessLockFactory $jobLockFactory,
        LoggerInterface $logger = null
    ) {
        $this->bus = $bus;
        $this->jobLockFactory = $jobLockFactory;
        $this->logger = $logger;
    }

    protected function setJob(Job $job): void
    {
        $lock = $this->jobLockFactory->createLock($job);

        // Avoid to set a job when the same job is already pending.
        if (!$lock->acquire()) {
            if ($this->logger) {
                $this->logger->warning(
                    '{command}: Job is already running, message not dispatched',
                    ['command' => $job->getCommand(), 'args' => $job->getArgs()]
                );
            }

            return;
        }

        $stamps = [];
        if ($job->isDelayed()) {
            $stamps[] = new DelayStamp($job->getDelay());
        }

        $this->bus->dispatch($job, $stamps);
        // lock will be released in job handler
    }
}
