<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Order\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Model\Cart;

class CartViewFactory
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var VatApplicable
     */
    private $vatApplicable;

    /**
     * OrderViewFactory constructor.
     *
     * @param GroupFactory  $groupFactory
     * @param VatApplicable $vatApplicable
     */
    public function __construct(GroupFactory $groupFactory, VatApplicable $vatApplicable)
    {
        $this->groupFactory  = $groupFactory;
        $this->vatApplicable = $vatApplicable;
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
            $this->vatApplicable->onCart($cart),
            $cart->getSheet()->getEvent()->getVat()
        );
    }
}
