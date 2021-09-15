<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch\BatchValidateCommand;

class BatchValidateJobQueue extends AbstractJobQueueAdapter implements BatchJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(array $ids, Admin $admin, array $options = [])
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Missing sheet ids parameters');
        }

        $arguments = [
            'sheetIds' => implode(',', $ids),
            'adminId' => $admin->getId(),
            'comment' => $options['comment']??null,
        ];

        $job = new Job(BatchValidateCommand::NAME, $arguments);

        $this->sendJob($job);
    }
}
