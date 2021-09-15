<?php

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
