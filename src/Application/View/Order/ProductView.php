<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class ProductView
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
     * @var ProductView[]
     */
    public $includedProducts = [];

    /**
     * @param int    $id
     * @param string $label
     * @param float  $price
     * @param int    $quantity
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct(
        $id,
        $label,
        $price,
        $quantity,
        $vatMode,
        $currency
    ) {
        $this->id       = $id;
        $this->label    = $label;
        $this->price    = $price;
        $this->quantity = $quantity;
        $this->total    = ($price * $quantity);
        $this->vatMode  = $vatMode;
        $this->currency = $currency;
    }

    /**
     * @param ProductView $productView
     */
    public function addIncludedProduct(ProductView $productView)
    {
        $this->includedProducts[] = $productView;
    }
}
