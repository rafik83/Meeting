<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class IncludedProductView
{
    /** @var string */
    private $label;

    /** @var int */
    private $quantity;

    /** @var int in cents */
    private $price;

    /** @var int in cents */
    private $total;

    /**
     * @param string $label
     * @param int    $quantity
     * @param int    $price
     * @param int    $total
     */
    public function __construct($label, $quantity, $price, $total)
    {
        $this->label    = $label;
        $this->quantity = $quantity;
        $this->price    = $price;
        $this->total    = $total;
    }
}
