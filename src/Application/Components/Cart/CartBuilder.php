<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

use Proximum\Vimeet\Application\Components\Cart\Carts\LibCartInterface;
use Proximum\Vimeet\Application\Components\Product\Template;

class CartBuilder
{
    /**
     * @var array
     */
    private $cartLibs = [];

    public function registerCart($name, LibCartInterface $cart)
    {
        $this->cartLibs[$name] = $cart;
    }

    /**
     * @param Template $template
     * @param array    $productData
     * @param string   $locale
     *
     * @return Cart
     */
    public function generate(Template $template, array $productData, $locale)
    {
        $cart = new Cart(0);

        if (empty($productData)) {
            return $cart;
        }

        foreach ($productData as $stepKey => $step) {
            $stepObject = $template->getStep($stepKey);
            $cartStep = new CartStep(
                $stepObject->getLabel($locale),
                0
            );

            foreach ($step as $productKey => $product) {
                $cartRow = null;
                $productObject = $stepObject->getProduct($productKey);

                if (method_exists($productObject, 'getType')) {
                    if (isset($this->cartLibs[$productObject->getType()]) && [] !== $product) {
                        $cartRow = $this->cartLibs[$productObject->getType()]->prepare(
                            $productObject,
                            $product,
                            $locale
                        );
                    }
                }

                if (null !== $cartRow) {
                    $cartStep->addCartRow($cartRow);
                }
            }

            $cart->addCartStep($cartStep);
        }

        return $cart;
    }
}
