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

class BatchValidate extends AbstractBatch
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
     * @var string
     */
    public $comment;

    /**
     * BatchValidate constructor.
     *
     * @param array  $ids
     * @param Admin  $admin
     * @param string $comment
     */
    public function __construct(array $ids, Admin $admin, $comment)
    {
        $this->ids     = $ids;
        $this->admin   = $admin;
        $this->comment = $comment;
    }
}
