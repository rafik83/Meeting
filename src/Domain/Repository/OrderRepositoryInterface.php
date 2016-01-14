<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

interface OrderRepositoryInterface
{
    /**
     * @param Order $order
     */
    public function add(Order $order);

    /**
     * @param int   $page
     * @param int   $limit
     * @param Sheet $sheet
     *
     * @return mixed
     */
    public function paginate($page, $limit, Sheet $sheet);
}
