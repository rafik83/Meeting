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
use Proximum\Vimeet\Domain\Model\Type;

class BatchDuplicateSheets
{
    /** @var Admin */
    public $admin;

    /** @var Type */
    public $type;

    /** @var int[] */
    public $ids;

    public function __construct(Admin $admin, Type $type, array $ids)
    {
        $this->admin = $admin;
        $this->type = $type;
        $this->ids = $ids;
    }
}
