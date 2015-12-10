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

class LibPlanningCart extends LibAbstractCart
{
    /**
     * {@inheritdoc}
     */
    public function prepare(ProductInterface $product, array $dataValue, $locale)
    {
        $cartRow = null;

        if (null !== $product
            && isset($dataValue['planning'])
            && $dataValue['planning'] === true
            && isset($dataValue['planning_bought'])
            && $dataValue['planning_bought'] !== 0
        ) {
            $cartRow = new CartRow(
                $product->getLabel($locale),
                isset($dataValue['planning_bought'])
                && $dataValue['planning_bought'] !== null
                ? $dataValue['planning_bought'] : 0,
                $product->getUnitPrice()
            );

            if (!empty($product->getInclude())) {
                foreach ($product->getInclude() as $including) {
                    $cartRow->addInclude($this->including($product, $including, $locale));
                }
            }
        }

        return $cartRow;
    }
}
