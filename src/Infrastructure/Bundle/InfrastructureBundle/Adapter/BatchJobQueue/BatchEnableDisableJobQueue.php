<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch\BatchCatalogCommand;

class BatchEnableDisableJobQueue extends AbstractJobQueueAdapter implements BatchJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(array $ids, Admin $admin, $options = [])
    {
        $job = new Job(BatchCatalogCommand::NAME, [
            implode(',', $ids),
            $admin->getId(),
            isset($options['state']) ? $options['state'] : null,
        ]);

        $this->setJob($job);
    }
}
