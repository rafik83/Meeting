<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;

class RemoveRow
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var Order\Row
     */
    public $row;

    /**
     * RemoveRow constructor.
     *
     * @param Order     $order
     * @param Order\Row $row
     */
    public function __construct(Order $order, Order\Row $row)
    {
        $this->order = $order;
        $this->row = $row;
    }
}
