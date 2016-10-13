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

class Batch extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $validate;

    /**
     * @var bool
     */
    public $accept;

    /**
     * @var bool
     */
    public $assign;

    /**
     * @var bool
     */
    public $enable;

    /**
     * @var bool
     */
    public $disable;

    /**
     * @var Admin
     */
    public $follower;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $validateComment;

    /**
     * @var bool
     */
    public $addCatalog;

    /**
     * @var bool
     */
    public $removeCatalog;

    /**
     * @var bool
     */
    public $draft;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
