<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\CartRow;
use Proximum\Vimeet\Application\Components\Product\Including;
use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;

interface LibCartInterface
{
    /**
     * @param ProductInterface  $product
     * @param array             $dataValue
     * @param string            $locale
     *
     * @return CartRow|null
     */
    public function prepare(ProductInterface $product, array $dataValue, $locale);

    /**
     * @param ProductInterface $product
     * @param Including        $including
     * @param string           $locale
     *
     * @return CartRow|null
     */
    public function including(ProductInterface $product, Including $including, $locale);
}
