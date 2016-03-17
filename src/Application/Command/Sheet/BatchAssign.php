<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchAssign
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchAssign constructor.
     *
     * @param array $ids
     * @param Admin $admin
     */
    public function __construct(array $ids, Admin $admin)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
    }
}
