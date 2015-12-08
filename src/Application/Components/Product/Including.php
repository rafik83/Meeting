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
    private $includer;

    /**
     * @var ProductInterface
     */
    private $include;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @param ProductInterface $includer
     * @param ProductInterface $include
     * @param float            $quantity
     */
    public function __construct(ProductInterface $includer, ProductInterface $include, $quantity)
    {
        $this->includer = $includer;
        $this->include  = $include;
        $this->quantity = $quantity;

        $includer->addInclude($this);
        $include->addIncludedIn($this);
    }

    /**
     * @return ProductInterface
     */
    public function getIncluder()
    {
        return $this->includer;
    }

    /**
     * @return ProductInterface
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
