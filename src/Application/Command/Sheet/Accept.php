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
use Proximum\Vimeet\Domain\Model\Sheet;

class Accept
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var DateTimeInterface
     */
    public $date;

    /**
     * Accept constructor.
     *
     * @param Sheet             $sheet
     * @param Admin             $admin
     * @param DateTimeInterface $date
     */
    public function __construct(Sheet $sheet, Admin $admin, DateTimeInterface $date)
    {
        $this->sheet = $sheet;
        $this->admin = $admin;
        $this->date  = $date;
    }
}
