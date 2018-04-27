<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class StepOption
{
    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * StepOption constructor.
     *
     * @param Merger      $orderMerger
     * @param CartManager $cartManager
     */
    public function __construct(Merger $orderMerger, CartManager $cartManager)
    {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
    }

    /**
     * @param Sheet $sheet
     * @param int   $stepIndex
     *
     * @return SelectOptions
     */
    public function build(Sheet $sheet, $stepIndex)
    {
        $command     = new SelectOptions($sheet, $stepIndex);
        $cart        = $this->cartManager->getCart($command->sheet, $command->currentStep);
        $orderMerged = null;

        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->orderMerger->merge($sheet->getNotCancelledOrders());
        }

        /** @var CartRow[] $optionRows */
        $cartRows = array_combine(
            array_map(
                function (CartRow $cartRow) {
                    return $cartRow->getProduct()->getId();
                },
                $cart->getOptionsRow()->toArray()
            ),
            $cart->getOptionsRow()->toArray()
        );

        $options          = [];
        $availableOptions = $command->sheet->getPackage()->getAvailablesOptions(new \DateTime());

        foreach ($availableOptions as $option) {
            $options[$option->getId()] = $this->getOptionQuantity($option, $cartRows, $orderMerged);
        }

        $command->options = $options;

        return $command;
    }

    /**
     * @param Product    $option
     * @param array      $cartRows
     * @param null|Order $order
     *
     * @return int
     */
    private function getOptionQuantity(Product $option, array $cartRows = [], Order $order = null)
    {
        $cartQuantity = 0;
        $cartRow      = $this->getCartRowFromOption($option, $cartRows);

        if (null !== $cartRow) {
            $cartQuantity = $cartRow->getQuantity();
        }
        $optionQuantity = $cartQuantity;

        if (isset($order) && $product = $order->getRowForProduct($option)) {
            $orderQuantity  = $product->getQuantity();
            $optionQuantity = $orderQuantity;

            if (null !== $cartRow) {
                $optionQuantity = $orderQuantity + $cartQuantity;
            }
        }

        return $optionQuantity;
    }

    /**
     * @param Product $option
     * @param array   $cartRows
     *
     * @return CartRow|null
     */
    private function getCartRowFromOption(Product $option, array $cartRows)
    {
        if (isset($cartRows[$option->getId()])) {
            return $cartRows[$option->getId()];
        }

        return null;
    }
}
