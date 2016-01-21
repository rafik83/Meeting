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
     * @var string
     */
    public $group;

    /**
     * @var string
     */
    public $row;

    /**
     * RemoveRow constructor.
     *
     * @param Order  $order
     * @param string $group
     * @param string $row
     */
    public function __construct(Order $order, $group, $row)
    {
        $this->order = $order;
        $this->group = $group;
        $this->row   = $row;
    }
}
