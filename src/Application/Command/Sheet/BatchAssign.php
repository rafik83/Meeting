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

class BatchAssign extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var Admin|null
     */
    public $admin;

    /**
     * @var bool
     */
    public $unassigned;

    /**
     * BatchAssign constructor.
     *
     * @param array      $ids
     * @param Admin|null $admin
     * @param bool       $unassigned
     */
    public function __construct(array $ids, Admin $admin = null, $unassigned = false)
    {
        $this->ids        = $ids;
        $this->admin      = $admin;
        $this->unassigned = $unassigned;
    }
}
