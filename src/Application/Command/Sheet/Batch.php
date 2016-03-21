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

class Batch
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
     * @var Admin
     */
    public $follower;
}
