<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;

class CreateHandler
{
    /**
     * @var Cart\Converter
     */
    protected $converter;

    /**
     * @var Cart\CartManager
     */
    protected $cartManager;

    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * @param Cart\Converter     $converter
     * @param Cart\CartManager   $cartManager
     * @param \DateTimeInterface $datetime
     */
    public function __construct(
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        \DateTimeInterface $datetime
    ) {
        $this->converter   = $converter;
        $this->cartManager = $cartManager;
        $this->datetime    = $datetime;
    }

    /**
     * @param Create $create
     *
     * @throws MissingBillingInfoException
     */
    public function handle(Create $create)
    {
        $this->converter->toOrder($this->cartManager->getCart($create->sheet));
    }
}
