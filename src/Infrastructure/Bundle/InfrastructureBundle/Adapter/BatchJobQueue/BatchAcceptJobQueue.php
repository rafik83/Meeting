<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch\BatchAcceptCommand;

class BatchAcceptJobQueue extends AbstractJobQueueAdapter implements BatchJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(array $ids, Admin $admin, array $options = [])
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Missing sheet ids parameters');
        }

        $job = new Job(BatchAcceptCommand::NAME, [
            'sheetIds' => implode(',', $ids),
            'adminId' => $admin->getId(),
        ]);

        $this->sendJob($job);
    }
}
