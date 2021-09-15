<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Admin;

interface BatchJobQueueInterface
{
    /**
     * @param int[] $ids
     * @param Admin $admin
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function createJob(array $ids, Admin $admin, array $options = []);
}
