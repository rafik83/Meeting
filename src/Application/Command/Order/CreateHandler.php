<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Cart\Converter;

class CreateHandler
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * CreateHandler constructor.
     *
     * @param Converter   $converter
     * @param CartManager $cartManager
     */
    public function __construct(Converter $converter, CartManager $cartManager)
    {
        $this->converter   = $converter;
        $this->cartManager = $cartManager;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $this->converter->toOrder($this->cartManager->getCart($create->sheet));
    }
}
