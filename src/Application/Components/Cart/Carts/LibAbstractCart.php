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

class LibAbstractCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(ProductInterface $product, array $dataValue, $locale)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function including(Including $including, $locale)
    {
        $cartRow = null;

        if (null !== $including) {
            $cartRow = new CartRow(
                $including->getProductIncluded()->getLabel($locale),
                $including->getQuantity(),
                0
            );
        }

        return $cartRow;
    }
}
