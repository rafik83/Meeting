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
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param SelectPlan $plans
     */
    public function handle(SelectPlan $plans)
    {
        $cartRow = $this->cartManager->getCart($plans->sheet);
        $cartRow->setProduct($plans->plan, 1);
        $this->cartManager->save($cartRow);
    }
}
