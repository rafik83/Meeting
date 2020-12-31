<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch\BatchPendingCommand;

class BatchPendingJobQueue extends AbstractJobQueueAdapter implements BatchJobQueueInterface
{
    /**
     * @param int[] $ids
     * @param Admin $admin
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function createJob(array $ids, Admin $admin, array $options = [])
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Missing sheet ids parameters');
        }

        $job = new Job(BatchPendingCommand::NAME, [
            implode(',', $ids),
            $admin->getId(),
        ]);

        $this->setJob($job);
    }
}
