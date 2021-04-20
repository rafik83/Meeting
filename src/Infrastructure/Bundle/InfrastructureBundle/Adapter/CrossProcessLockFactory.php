<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\AbstractJob;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\PersistingStoreInterface;

class CrossProcessLockFactory
{
    private PersistingStoreInterface $jobLockStore;

    public function __construct(PersistingStoreInterface $jobLockStore)
    {
        $this->jobLockStore = $jobLockStore;
    }

    public function createLock(AbstractJob $job): Lock
    {
        return new Lock($job->getLockKey(), $this->jobLockStore, $job->getMaxExecutionTime(), false);
    }

    public function createLockForRelease(AbstractJob $job): Lock
    {
        return new Lock($job->getLockKey(), $this->jobLockStore);
    }
}
