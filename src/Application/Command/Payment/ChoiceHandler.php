<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Domain\Cart;

class ChoiceHandler
{
    /**
     * @var Cart\Converter
     */
    private $converter;

    /**
     * @var Cart\CartManager
     */
    private $cartManager;

    /**
     * @param Cart\Converter   $converter
     * @param Cart\CartManager $cartManager
     */
    public function __construct(Cart\Converter $converter, Cart\CartManager $cartManager)
    {
        $this->converter   = $converter;
        $this->cartManager = $cartManager;
    }

    /**
     * @param Choice $choice
     */
    public function handle(Choice $choice)
    {
        $this->converter->toOrder($this->cartManager->getCart($choice->sheet));

        // Create Transaction
    }
}
