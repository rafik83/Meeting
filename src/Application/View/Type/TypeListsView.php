<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Type;

use Proximum\Vimeet\Domain\Model\PaginatedResult;

class TypeListsView
{
    /**
     * @var TypeListView[]
     */
    public $types;

    /**
     * @var PaginatedResult
     */
    public $results;
}
