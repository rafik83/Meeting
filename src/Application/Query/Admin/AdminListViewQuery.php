<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Admin;

use Proximum\Vimeet\Application\Query\Query;

class AdminListViewQuery implements Query
{
    /** @var array */
    public $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
}
