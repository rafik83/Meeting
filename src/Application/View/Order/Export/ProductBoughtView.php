<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order\Export;

class ProductBoughtView
{
    /**
     * @param int   $productId
     * @param float $unitPrice
     * @param int   $quantity
     * @param float $total
     */
    public function __construct(
        $productId,
        $unitPrice,
        $quantity,
        $total
    ) {
        $this->productId = $productId;
        $this->unitPrice = $unitPrice;
        $this->quantity  = $quantity;
        $this->total     = $total;
    }
}
