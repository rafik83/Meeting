<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class StepOption
{
    /** @var Merger */
    private $orderMerger;

    /** @var CartManager */
    private $cartManager;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * StepOption constructor.
     *
     * @param Merger      $orderMerger
     * @param CartManager $cartManager
     */
    public function __construct(Merger $orderMerger, CartManager $cartManager, \DateTimeInterface $dateTime)
    {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
        $this->dateTime = $dateTime;
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
                $cart->getOptionsRowArray()
            ),
            $cart->getOptionsRowArray()
        );

        $options          = [];
        $availableOptions = $command->sheet->getPackage()->getAvailablesOptions($this->dateTime);

        foreach ($availableOptions as $option) {
            // @todo: second arguments must have previously selected participants
            $options[$option->getId()] = new OptionRow(
                $this->getOptionQuantity($option, $cartRows, $orderMerged),
                [],
                $option->isAttributable()
            );
        }

        $command->options = $options;

        return $command;
    }

    /**
     * @param Product    $option
     * @param CartRow[]  $cartRows
     * @param null|Order $order
     *
     * @return int
     */
    private function getOptionQuantity(Product $option, array $cartRows = [], Order $order = null): int
    {
        $optionQuantity = 0;
        $cartRow = $this->getCartRowFromOption($option, $cartRows);

        if (null !== $cartRow) {
            $optionQuantity = $cartRow->getQuantity();
        }

        if (!$order instanceof Order || !$orderRow = $order->getRowForProduct($option)) {
            return $optionQuantity;
        }

        return $orderRow->getQuantity() + $optionQuantity;
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
