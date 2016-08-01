<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class RowView
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
     * @var IncludedProductView[]
     */
    public $includedProducts = [];

    /***
     * @var null|CustomRowView[]
     */
    public $customRows = [];

    /**
     * @param int    $id
     * @param int    $productId
     * @param string $label
     * @param float  $price
     * @param int    $quantity
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct(
        $id,
        $productId,
        $label,
        $price,
        $quantity,
        $vatMode,
        $currency
    ) {
        $this->id        = $id;
        $this->productId = $productId;
        $this->label     = $label;
        $this->price     = $price;
        $this->quantity  = $quantity;
        $this->total     = $price * $quantity;
        $this->vatMode   = $vatMode;
        $this->currency  = $currency;
    }

    /**
     * @param IncludedProductView $productView
     */
    public function addIncludedProduct(IncludedProductView $productView)
    {
        $this->includedProducts[] = $productView;
    }

    /**
     * @param CustomRowView $customRowView
     */
    public function addCustomRow(CustomRowView $customRowView)
    {
        $this->customRows[] = $customRowView;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        foreach ($this->customRows as $row) {
            $this->total += $row->total;
        }
        return $this->total;
    }
}
