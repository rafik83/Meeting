<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product;

use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;

class Including
{
    /**
     * @var ProductInterface
     */
    private $productThatInclude;

    /**
     * @var ProductInterface
     */
    private $productIncluded;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @param ProductInterface $productThatInclude
     * @param ProductInterface $productIncluded
     * @param float            $quantity
     */
    public function __construct(ProductInterface $productThatInclude, ProductInterface $productIncluded, $quantity)
    {
        $this->productThatInclude = $productThatInclude;
        $this->productIncluded    = $productIncluded;
        $this->quantity = $quantity;

        $productThatInclude->addInclude($this);
        $productIncluded->addIncludedIn($this);
    }

    /**
     * @return ProductInterface
     */
    public function getProductThatInclude()
    {
        return $this->productThatInclude;
    }

    /**
     * @return ProductInterface
     */
    public function getProductIncluded()
    {
        return $this->productIncluded;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
