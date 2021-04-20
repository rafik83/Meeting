<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\PersistingStoreInterface;

class CrossProcessLockFactory
{
    private PersistingStoreInterface $jobLockStore;

    public function __construct(PersistingStoreInterface $jobLockStore)
    {
        $this->jobLockStore = $jobLockStore;
    }

    public function createLock(Job $job): Lock
    {
        return new Lock($job->getLockKey(), $this->jobLockStore, $job->getMaxExecutionTime(), false);
    }

    public function createLockForRelease(Job $job): Lock
    {
        return new Lock($job->getLockKey(), $this->jobLockStore);
    }
}
