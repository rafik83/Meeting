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
use Proximum\Vimeet\Domain\Model\Sheet;

class Validate
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
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var string
     */
    public $comment;

    /**
     * Validate constructor.
     *
     * @param Sheet              $sheet
     * @param Admin              $admin
     * @param \DateTimeInterface $date
     * @param string             $comment
     */
    public function __construct(Sheet $sheet, Admin $admin, \DateTimeInterface $date, $comment)
    {
        $this->sheet   = $sheet;
        $this->admin   = $admin;
        $this->date    = $date;
        $this->comment = $comment;
    }
}
