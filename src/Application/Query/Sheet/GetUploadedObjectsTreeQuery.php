<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class GetUploadedObjectsTreeQuery implements Query
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    public function __construct(array $sheets, Admin $admin)
    {
        $this->sheets = $sheets;
        $this->admin = $admin;
    }
}
