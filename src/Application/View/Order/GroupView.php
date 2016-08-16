<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Domain\Model\Sheet;

class GroupView
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $type;

    /**
     * @var ProductView[]
     */
    public $products = [];

    /**
     * @var null|int
     */
    public $stepIndex;

    /**
     * @param Sheet         $sheet
     * @param string        $label
     * @param string        $type
     * @param ProductView[] $products
     * @param null|int      $stepIndex
     */
    public function __construct(Sheet $sheet, $label, $type, array $products = [], $stepIndex = null)
    {
        $this->sheet     = $sheet;
        $this->label     = $label;
        $this->type      = $type;
        $this->products  = $products;
        $this->stepIndex = $stepIndex;
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
}
