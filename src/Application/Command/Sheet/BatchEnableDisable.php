<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchEnableDisable extends AbstractBatch
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
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * BatchValidate constructor.
     *
     * @param array              $ids
     * @param bool               $state
     * @param Admin              $admin
     * @param \DateTimeInterface $date
     */
    public function __construct(array $ids, $state, Admin $admin, \DateTimeInterface $date)
    {
        $this->ids   = $ids;
        $this->state = $state;
        $this->admin = $admin;
        $this->date  = $date;
    }
}
