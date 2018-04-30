<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
