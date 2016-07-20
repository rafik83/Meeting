<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class GroupView
{
    /**
     * @var int
     */
    public $groupId;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $products = [];

    /**
     * @var array
     */
    public $customRows = [];

    /**
     * @param string $label
     * @param string $type
     * @param int    $groupId
     * @param array  $products
     * @param array  $customRows
     */
    public function __construct($label, $type, $groupId, array $products = [], array  $customRows = [])
    {
        $this->label      = $label;
        $this->type       = $type;
        $this->products   = $products;
        $this->groupId    = $groupId;
        $this->customRows = $customRows;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->products, function ($carry, ProductView $product) {
            return $carry + $product->total;
        }, 0);
    }

    /**
     * @param ProductView $product
     */
    public function addProduct(ProductView $product)
    {
        $this->products[] = $product;
    }

    /**
     * @param CustomRowView $customRow
     */
    public function addCustomRow(CustomRowView $customRow)
    {
        $this->customRows[] = $customRow;
    }
}
