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
        $total = array_reduce($this->products, function ($carry, RowView $product) {
            return $carry + $product->total;
        }, 0);

        $total += array_reduce($this->customRows, function ($carry, CustomRowView $product) {
            return $carry + $product->total;
        }, 0);

        return $total;
    }

    /**
     * @param RowView $product
     */
    public function addProduct(RowView $product)
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
