<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Product;

class ProductViewQuery
{
    /** @var Product */
    public $product;

    /** @var string */
    public $locale;

    /** @var string */
    public $adminLocale;

    /**
     * @param Product $product
     * @param string  $locale
     * @param string  $adminLocale
     */
    public function __construct(Product $product, $locale, $adminLocale)
    {
        $this->product     = $product;
        $this->locale      = $locale;
        $this->adminLocale = $adminLocale;
    }
}
