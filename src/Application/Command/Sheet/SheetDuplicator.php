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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetDuplicator
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    /** @var Type */
    public $type;

    public function __construct(array $sheets, Admin $admin, Type $type)
    {
        $this->sheets = $sheets;
        $this->admin = $admin;
        $this->type = $type;
    }
}
