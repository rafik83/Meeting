<?php

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
