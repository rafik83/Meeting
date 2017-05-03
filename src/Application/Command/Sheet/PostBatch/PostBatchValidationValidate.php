<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchValidationValidate
{
    /**
     * @var Sheet[]
     */
    public $sheets;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * PostBatchValidationValidate constructor.
     *
     * @param Sheet[] $sheets
     * @param Admin   $admin
     */
    public function __construct(array $sheets, Admin $admin)
    {
        $this->sheets = $sheets;
        $this->admin  = $admin;
    }
}
