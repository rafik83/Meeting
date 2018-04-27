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

class BatchCatalog extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $state;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchCatalog constructor.
     *
     * @param array $ids
     * @param bool  $state
     * @param Admin $admin
     */
    public function __construct(array $ids, $state, Admin $admin)
    {
        $this->ids   = $ids;
        $this->state = $state;
        $this->admin = $admin;
    }
}
