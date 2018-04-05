<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CreateHandler
{
    /** @var Cart\Converter */
    protected $converter;

    /** @var Cart\CartManager */
    protected $cartManager;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    protected $datetime;

    /**
     * @param Cart\Converter         $converter
     * @param Cart\CartManager       $cartManager
     * @param DelayedEventDispatcher $eventDispatcher
     * @param \DateTimeInterface     $datetime
     */
    public function __construct(
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->converter       = $converter;
        $this->cartManager     = $cartManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param Create $create
     *
     * @throws MissingBillingInfoException
     */
    public function handle(Create $create)
    {
        $order = $this->converter->toOrder($this->cartManager->getCart($create->sheet));

        $event = new OrderConfirmEvent($order, $create->user);
        $this->eventDispatcher->dispatch(Events::ORDER_CONFIRMED, $event);

        // trigger event to remove must select package notification after first order
        $mustSelectPackageEvent = new MustSelectPackageEvent($create->sheet);
        $this->eventDispatcher->dispatch(Events::MUST_SELECT_PACKAGE, $mustSelectPackageEvent);
    }
}
