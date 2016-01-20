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

class UpdateRow
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
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $price;

    /**
     * @var int
     */
    public $quantity;

    /**
     * UpdateRow constructor.
     *
     * @param Order  $order
     * @param string $group
     * @param string $row
     */
    public function __construct(Order $order, $group, $row)
    {
        $packageTemplate   = $order->getPackageTemplate();
        $packageData       = $order->getPackageData();
        $this->order       = $order;
        $this->group       = $group;
        $this->row         = $row;
        $this->label       = $packageTemplate[$group]['template'][$row]['label'];
        $this->description = $packageTemplate[$group]['template'][$row]['description'];
        $this->price       = $packageTemplate[$group]['template'][$row]['unitPrice'];
        $this->quantity    = $packageData[$group][$row]['quantity'];
    }
}
