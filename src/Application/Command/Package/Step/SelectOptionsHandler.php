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
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @param CartManager        $cartManager
     * @param \DateTimeInterface $now
     */
    public function __construct(CartManager $cartManager, \DateTimeInterface $now)
    {
        $this->cartManager = $cartManager;
        $this->now = $now;
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
            $package->getAvailablesOptions($this->now)
        );

        $options = array_combine($ids, $package->getAvailablesOptions($this->now));

        $cart->clearOptions();

        foreach ($selectOptions->options as $id => $quantity) {
            $cart->setProduct($options[$id], $quantity);
        }

        $this->cartManager->save($cart);
    }
}
