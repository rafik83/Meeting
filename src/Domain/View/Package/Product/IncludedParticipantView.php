<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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

    /** @var int */
    public $remainingQuantity;

    /**
     * @param Product $product
     * @param int     $totalQuantity
     * @param int     $remainingQuantity
     */
    public function __construct(Product $product, int $totalQuantity, int $remainingQuantity)
    {
        $this->product = $product;
        $this->totalQuantity = $totalQuantity;
        $this->remainingQuantity = $remainingQuantity;
    }
}
