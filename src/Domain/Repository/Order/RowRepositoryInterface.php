<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Order;

use Proximum\Vimeet\Domain\Model\Order\Row;

interface RowRepositoryInterface
{
    /**
     * @param Row $row
     */
    public function set(Row $row);

    /**
     * @param Row $row
     */
    public function remove(Row $row);
}
