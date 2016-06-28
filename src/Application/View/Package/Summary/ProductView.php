<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class ProductView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $total = 0;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var IncludedView[]
     */
    public $included;

    /**
     * @param int    $id
     * @param string $title
     * @param float  $unitPrice
     * @param int    $quantity
     * @param float  $total
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct(
        $id,
        $title,
        $unitPrice,
        $quantity,
        $total,
        $vatMode,
        $currency
    ) {
        $this->id        = $id;
        $this->title     = $title;
        $this->unitPrice = $unitPrice;
        $this->quantity  = $quantity;
        $this->total     = $total;
        $this->vatMode   = $vatMode;
        $this->currency  = $currency;
    }

    /**
     * @param IncludedView $included
     *
     * @return ProductView
     */
    public function addIncludedProduct(IncludedView $included)
    {
        $this->included[] = $included;

        return $this;
    }
}
