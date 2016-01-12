<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Type\Order;

class RowView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $quantity;

    /**
     * RowView constructor.
     *
     * @param string $label
     * @param float  $unitPrice
     * @param int    $quantity
     */
    public function __construct($label, $unitPrice, $quantity)
    {
        $this->label     = $label;
        $this->unitPrice = $unitPrice;
        $this->quantity  = $quantity;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->unitPrice * $this->quantity;
    }
}
