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
     * @param array  $ids
     * @param Admin  $admin
     * @param string $locale
     */
    public function createJob(array $ids, Admin $admin, $locale);
}
