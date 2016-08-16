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

class SelectPlanHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * SelectPlanHandler constructor.
     *
     * @param CartManager           $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param SelectPlan $selectPlan
     */
    public function handle(SelectPlan $selectPlan)
    {
        $cart = $this->cartManager->getCart($selectPlan->sheet, $selectPlan->currentStep);

        $previousPlan = $cart->getPlanRow();

        if (null !== $previousPlan && $previousPlan->getProduct() !== $selectPlan->plan) {
            $cart->clear();
        }

        if (null === $previousPlan || $previousPlan->getProduct() !== $selectPlan->plan) {
            $this->cartManager->deleteCartStep($cart);
            $cart->setProduct($selectPlan->plan, 1);
            $this->cartManager->save($cart);
        }
    }
}
