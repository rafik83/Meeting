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
     * @param Order     $order
     * @param Order\Row $row
     * @param string    $locale
     */
    public function __construct(Order $order, Order\Row $row, $locale)
    {
        $this->order       = $order;
        $this->row         = $row;
        $this->label       = $row->getLabel($locale);
        $this->price       = $row->getPrice();
        $this->quantity    = $row->getQuantity();
    }
}
