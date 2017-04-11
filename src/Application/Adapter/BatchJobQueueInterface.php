<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Admin;

interface BatchJobQueueInterface
{
    /**
     * @param int[]  $ids
     * @param Admin  $admin
     * @param array  $options
     */
    public function createJob(array $ids, Admin $admin, array $options = []);
}
