<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Model\Admin;

class Find
{
    /**
     * @var string
     */
    public $beginDate;
    
    /**
     * @var string
     */
    public $endDate;
    
    /**
     * @var bool
     */
    public $paid = true;
    
    /**
     * @var Admin
     */
    public $admin;
    
    /**
     * Find constructor.
     *
     * @param $admin
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
    }
}
