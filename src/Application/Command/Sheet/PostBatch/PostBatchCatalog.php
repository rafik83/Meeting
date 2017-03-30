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

class PostBatchCatalog
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
     * @var bool
     */
    public $state;

    /**
     * PostBatchCatalogHandler constructor.
     *
     * @param Sheet[] $sheets
     * @param Admin   $admin
     * @param bool    $state
     */
    public function __construct(array $sheets, Admin $admin, $state)
    {
        $this->sheets = $sheets;
        $this->admin  = $admin;
        $this->state  = $state;
    }
}
