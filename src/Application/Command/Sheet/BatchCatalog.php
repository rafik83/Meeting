<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;

class BatchCatalog extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var DateTimeInterface
     */
    public $date;

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
     * @param array             $ids
     * @param DateTimeInterface $date
     * @param bool              $state
     * @param Admin             $admin
     */
    public function __construct(array $ids, DateTimeInterface $date, $state, Admin $admin)
    {
        $this->ids   = $ids;
        $this->date  = $date;
        $this->state = $state;
        $this->admin = $admin;
    }
}
