<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectOptionsHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;
    /**
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param CartManager            $cartManager
     * @param \DateTimeInterface     $now
     * @param Merger                 $merger
     * @param DelayedEventDispatcher $eventDispatcher
     */
    public function __construct(
        CartManager $cartManager,
        \DateTimeInterface $now,
        Merger $merger,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->cartManager     = $cartManager;
        $this->now             = $now;
        $this->merger          = $merger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param SelectOptions $selectOptions
     */
    public function handle(SelectOptions $selectOptions)
    {
        $sheet   = $selectOptions->sheet;
        $package = $sheet->getPackage();
        $cart    = $this->cartManager->getCart($sheet, $selectOptions->currentStep);

        $ids = array_map(
            function (Product $product) {
                return $product->getId();
            },
            $package->getAvailablesOptions($this->now)
        );

        $options = array_combine($ids, $package->getAvailablesOptions($this->now));

        $cart->clearOptions();

        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->merger->merge($sheet->getNotCancelledOrders());
        }

        foreach ($selectOptions->options as $id => $quantity) {
            $orderQuantity = 0;

            // handle new order
            if (isset($orderMerged)) {
                if ($product = $orderMerged->getRowByProductId($id)) {
                    $orderQuantity = $product->getQuantity();
                }
            }

            $cart->setProduct($options[$id], $quantity - $orderQuantity);
        }

        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectOptions->sheet);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }
}
