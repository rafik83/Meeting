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
use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;

class LibOptionCart extends LibAbstractCart
{
    /**
     * {@inheritdoc}
     */
    public function prepare(ProductInterface $product, array $dataValue, $locale)
    {
        $cartRow = null;

        if (null !== $product
            && !empty($product->getOptions())
            && isset($dataValue['value'])
            && $dataValue['value'] !== false
        ) {
            $cartRow = new CartRow(
                $product->getLabel($locale),
                isset($dataValue['quantity']) && $dataValue['quantity'] !== null ? $dataValue['quantity'] : 1,
                $product->getUnitPrice()
            );

            if (!empty($product->getInclude())) {
                foreach ($product->getInclude() as $including) {
                    $cartRow->addInclude($this->including($including, $locale));
                }
            }
        }

        return $cartRow;
    }
}
