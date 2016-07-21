<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;


class CustomRowView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var int
     */
    public $productId;

    /**
     * @param int      $id
     * @param string   $label
     * @param float    $price
     * @param int      $quantity
     * @param null|int $productId
     */
    public function __construct(
        $id,
        $label,
        $price,
        $quantity,
        $productId = null
    ) {
        $this->id        = $id;
        $this->label     = $label;
        $this->price     = $price;
        $this->quantity  = $quantity;
        $this->total     = $price * $quantity;
        $this->productId = $productId;
    }
}
