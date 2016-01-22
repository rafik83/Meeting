<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Domain\Model\Cart;

class CartViewFactory
{
    /**
     * @var OrderViewFactory
     */
    private $orderViewFactory;

    /**
     * CartViewFactory constructor.
     *
     * @param OrderViewFactory $orderViewFactory
     */
    public function __construct(OrderViewFactory $orderViewFactory)
    {
        $this->orderViewFactory = $orderViewFactory;
    }

    /**
     * @param Cart   $cart
     * @param string $locale
     *
     * @return CartView
     */
    public function createFromCart(Cart $cart, $locale)
    {
        return new CartView(
            $this->orderViewFactory->createGroupsFromArray($cart->getTemplate(), $cart->getData(), $locale),
            $cart->getSheet()->getEvent()->getMode(),
            $cart->getSheet()->getEvent()->getVat()
        );
    }
}
