<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Domain\Cart;
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
    protected $dateTime;

    /**
     * @param Cart\Converter         $converter
     * @param Cart\CartManager       $cartManager
     * @param DelayedEventDispatcher $eventDispatcher
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->converter       = $converter;
        $this->cartManager     = $cartManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    public function handle(Create $create): void
    {
        $order = $this->converter->toOrder($this->cartManager->getCart($create->sheet));

        $event = new OrderConfirmEvent($order, $create->user);
        $this->eventDispatcher->dispatch(Events::ORDER_CONFIRMED, $event);

        // trigger event to remove must select package notification after first order
        $mustSelectPackageEvent = new MustSelectPackageEvent($create->sheet);
        $this->eventDispatcher->dispatch(Events::MUST_SELECT_PACKAGE, $mustSelectPackageEvent);
    }
}
