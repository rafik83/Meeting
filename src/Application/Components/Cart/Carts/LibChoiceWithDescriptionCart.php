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

class LibChoiceWithDescriptionCart extends LibAbstractCart
{
    /**
     * {@inheritdoc}
     */
    public function prepare(ProductInterface $product, array $dataValue, $locale)
    {
        $cartRow = null;

        if (null !== $product
            && !empty($product->getOptions())
            && isset($dataValue)
            && isset($dataValue['value'])
        ) {
            $cartRow = new CartRow(
                $product->getLabel($locale) . ' : ' . $product->getChoice($dataValue['value'])->getLabel($locale),
                isset($template['choices'][$dataValue['value']]['quantity'])
                ? $template['choices'][$dataValue['value']]['quantity'] : 1,
                $product->getChoice($dataValue['value'])->getUnitPrice()
            );

            if (!empty($product->getChoice($dataValue['value'])->getInclude())) {
                foreach ($product->getChoice($dataValue['value'])->getInclude() as $including) {
                    $cartRow->addInclude($this->including($including, $locale));
                }
            }
        }


        return $cartRow;
    }
}
