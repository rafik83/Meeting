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
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * CartViewFactory constructor.
     *
     * @param GroupFactory $groupFactory
     */
    public function __construct(GroupFactory $groupFactory)
    {
        $this->groupFactory = $groupFactory;
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
            $this->groupFactory->createGroupsFromArray($cart->getTemplate(), $cart->getData(), $locale),
            $cart->getSheet()->getEvent()->getMode(),
            $cart->getSheet()->getEvent()->getVat()
        );
    }
}
