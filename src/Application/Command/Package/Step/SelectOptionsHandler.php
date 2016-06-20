<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;

class SelectOptionsHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param SelectOptions $selectOptions
     */
    public function handle(SelectOptions $selectOptions)
    {
        $sheet   = $selectOptions->sheet;
        $package = $sheet->getPackage();
        $cart    = $this->cartManager->getCart($sheet);

        $ids = array_map(
            function (Product $product) {
                return $product->getId();
            },
            $package->getAvailablesOptions()
        );

        $options = array_combine($ids, $package->getAvailablesOptions());

        foreach ($selectOptions->options as $id => $quantity) {
            $cart->setProduct($options[$id], $quantity);
        }

        $this->cartManager->save($cart);
    }
}
