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
     * @param array $packageTemplate
     * @param array $packageData
     * @param $locale
     *
     * @return Cart
     */
    public function create(array $packageTemplate, array $packageData, $locale)
    {
        $cart = new Cart(0);

        foreach ($packageTemplate as $blockKey => $block) {
            $cartStep = new CartStep(
                isset($block['title'][$locale]) ? $block['title'][$locale] : '',
                0
            );

            foreach ($block['template'] as $templateKey => $template) {
                $cartRow = null;

                if (isset($template['type'])) {
                    $dataValue = isset($packageData[$blockKey][$templateKey]) ? $packageData[$blockKey][$templateKey] : [];

                    if (isset($this->cartLibs[$template['type']]) && [] !== $dataValue) {
                        $cartRow = $this->cartLibs[$template['type']]->prepare($template, $dataValue, $locale);
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
