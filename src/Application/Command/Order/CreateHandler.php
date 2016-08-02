<?php

/*
<<<<<<< HEAD
 * This file is part of the vimeet project.
=======
 * This file is part of the Proximum Vimeet project.
>>>>>>> master
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

<<<<<<< HEAD
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Cart\Converter;
=======
use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
>>>>>>> master

class CreateHandler
{
    /**
<<<<<<< HEAD
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
=======
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
>>>>>>> master
    }

    /**
     * @param Create $create
<<<<<<< HEAD
=======
     *
     * @throws MissingBillingInfoException
>>>>>>> master
     */
    public function handle(Create $create)
    {
        $this->converter->toOrder($this->cartManager->getCart($create->sheet));
    }
}
