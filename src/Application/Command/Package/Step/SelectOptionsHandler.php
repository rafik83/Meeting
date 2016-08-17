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
use Proximum\Vimeet\Domain\Order\Merger;

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
     * @var Merger
     */
    private $merger;

    /**
     * @param CartManager        $cartManager
     * @param \DateTimeInterface $now
     * @param Merger             $merger
     */
    public function __construct(CartManager $cartManager, \DateTimeInterface $now, Merger $merger)
    {
        $this->cartManager = $cartManager;
        $this->now         = $now;
        $this->merger      = $merger;
    }

    /**
     * @param SelectOptions $selectOptions
     */
    public function handle(SelectOptions $selectOptions)
    {
        $sheet   = $selectOptions->sheet;
        $package = $sheet->getPackage();
        $cart    = $this->cartManager->getCart($sheet, $selectOptions->currentStep);

        $ids = array_map(
            function (Product $product) {
                return $product->getId();
            },
            $package->getAvailablesOptions($this->now)
        );

        $options = array_combine($ids, $package->getAvailablesOptions($this->now));

        $cart->clearOptions();

        if ($sheet->hasOrders()) {
            $orderMerged = $this->merger->merge($sheet->getOrders());
        }

        foreach ($selectOptions->options as $id => $quantity) {
            $orderQuantity = 0;

            // handle new order
            if (isset($orderMerged)) {
                if ($product = $orderMerged->getRowByProductId($id)) {
                    $orderQuantity = $product->getQuantity();
                }
            }

            $cart->setProduct($options[$id], $quantity - $orderQuantity);
        }

        $this->cartManager->save($cart);
    }
}
