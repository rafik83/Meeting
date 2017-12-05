<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
