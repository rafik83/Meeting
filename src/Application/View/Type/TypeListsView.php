<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
