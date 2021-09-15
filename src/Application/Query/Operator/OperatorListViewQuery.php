<?php

namespace Proximum\Vimeet\Application\Query\Operator;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;

class OperatorListViewQuery implements Query
{
    /** @var Admin */
    public $organizer;

    /** @var array */
    public $filters;

    public function __construct(Admin $organizer, array $filters = [])
    {
        $this->organizer = $organizer;
        $this->filters = $filters;
    }
}
