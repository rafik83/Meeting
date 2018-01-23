<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchPending extends AbstractBatch
{
    /**
     * @var int[]
     */
    public $ids;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchPending constructor.
     *
     * @param int[] $ids
     * @param Admin $admin
     */
    public function __construct(array $ids, Admin $admin)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
    }
}
