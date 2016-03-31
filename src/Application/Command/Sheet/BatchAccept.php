<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;

class BatchAccept
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
     * @var DateTimeInterface
     */
    public $date;

    /**
     * BatchAccept constructor.
     *
     * @param array             $ids
     * @param Admin             $admin
     * @param DateTimeInterface $date
     */
    public function __construct(array $ids, Admin $admin, DateTimeInterface $date)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
        $this->date  = $date;
    }
}
