<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
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
        $command = new SelectOptions($sheet, $stepIndex);
        $cart    = $this->cartManager->getCart($command->sheet, $command->currentStep);

        if ($command->sheet->hasOrders()) {
            $orderMerged = $this->orderMerger->merge($command->sheet->getOrders());
        }

        /** @var CartRow[] $optionRows */
        $optionRows = array_combine(
            array_map(
                function (CartRow $cartRow) {
                    return $cartRow->getProduct()->getId();
                },
                $cart->getOptionsRow()->toArray()
            ),
            $cart->getOptionsRow()->toArray()
        );

        $options = [];

        dump($command->sheet->getPackage()->getAvailablesOptions(new \DateTime()));

        foreach ($command->sheet->getPackage()->getAvailablesOptions(new \DateTime()) as $option) {
            $orderQuantity = 0;
            $cartQuantity  = 0;

            if (isset($orderMerged) && $product = $orderMerged->getRowForProduct($option)) {
                $orderQuantity = $product->getQuantity();
            } elseif (isset($optionRows[$option->getId()])) {
                $cartQuantity = $optionRows[$option->getId()]->getQuantity();
            }

            $options[$option->getId()] = $orderQuantity + $cartQuantity;
        }

        $command->options = $options;

        return $command;
    }
}
