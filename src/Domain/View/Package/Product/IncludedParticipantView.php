<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Package\Product;

use Proximum\Vimeet\Domain\Model\Product;

class IncludedParticipantView
{
    /** @var Product */
    public $product;

    /** @var int */
    public $totalQuantity;

    /**
     * @param Product $product
     * @param int     $totalQuantity
     */
    public function __construct(Product $product, int $totalQuantity)
    {
        $this->product = $product;
        $this->totalQuantity = $totalQuantity;
    }
}
