<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Query\Query;

interface QueryBusInterface
{
    /**
     * @param Query $query
     *
     * @return mixed
     */
    public function handle(Query $query);
}
