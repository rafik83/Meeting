<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch\BatchDuplicateSheetsCommand;

class BatchDuplicateSheetsJobQueue extends AbstractJobQueueAdapter implements BatchJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(array $ids, Admin $admin, array $options = [])
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Missing sheet ids parameters');
        }

        $job = new Job(BatchDuplicateSheetsCommand::NAME, [
            'adminId' => $admin->getId(),
            'typeId' => $options['typeId'] ?? null,
            'extraDataId' => $options['extraDataId'] ?? null,
            'originalEventId' => $options['originalEventId'] ?? null,
        ]);

        $this->setJob($job);
    }
}
