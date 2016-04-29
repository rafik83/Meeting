<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;

class BatchValidate
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
     * @var string
     */
    public $comment;

    /**
     * BatchValidate constructor.
     *
     * @param array             $ids
     * @param Admin             $admin
     * @param DateTimeInterface $date
     * @param string            $comment
     */
    public function __construct(array $ids, Admin $admin, DateTimeInterface $date, $comment)
    {
        $this->ids     = $ids;
        $this->admin   = $admin;
        $this->date    = $date;
        $this->comment = $comment;
    }
}
