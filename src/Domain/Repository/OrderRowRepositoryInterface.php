<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;


use Proximum\Vimeet\Domain\Model\Order\Row;

interface OrderRowRepositoryInterface
{
    /**
     * @param Row $order
     */
    public function remove(Row $order);
}
