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

class AddRow
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $price;

    /**
     * @var int
     */
    public $quantity = 1;

    /**
     * @var int
     */
    public $groupId;

    /**
     * @var int
     */
    public $productId;

    /**
     * AddRow constructor.
     *
     * @param Order    $order
     * @param string   $groupId
     * @param null|int $productId
     */
    public function __construct(Order $order, $groupId, $productId = null)
    {
        $this->order     = $order;
        $this->groupId   = $groupId;
        $this->productId = $productId;
    }
}
