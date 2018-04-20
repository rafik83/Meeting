<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class QuantityMaxGuesser
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @param CartManager $cartManager
     * @param Merger      $merger
     */
    public function __construct(CartManager $cartManager, Merger $merger)
    {
        $this->cartManager = $cartManager;
        $this->merger      = $merger;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getMaxPlanning(Sheet $sheet)
    {
        $planning = $sheet->getPackage()->getPlanning();

        if (!$planning) {
            return 0;
        }

        $cart              = $this->cartManager->getCart($sheet);
        $selectedPlan      = ($cart->getPlanRow() !== null) ? $cart->getPlanRow()->getProduct() : null;
        $countParticipants = $sheet->getParticipants()->count();
        $remainingQuantity = INF;

        if ($sheet->hasNotCancelledOrders()) {
            $order        = $this->merger->merge($sheet->getNotCancelledOrders());
            $selectedPlan = $order->getPlan();
        }

        if ($selectedPlan) {
            $includedPlanningProduct = $selectedPlan->getIncludedPlanningProduct();

            if ($includedPlanningProduct) {
                $remainingQuantity = $countParticipants - $includedPlanningProduct->getQuantity();
            }
        }

        $max = min(
            $remainingQuantity,
            $countParticipants,
            $planning->getQuantityMax(),
            $planning->getAvailability()
        );

        return $max < 0 ? 0 : $max;
    }

    /**
     * @param Sheet   $sheet
     * @param Product $product
     *
     * @return int
     */
    public function getMaxByProduct(Sheet $sheet, Product $product)
    {
        if ($product->isAttributable()) {
            return min(
                $product->getQuantityMax(),
                $product->getAvailability()
            );
        }

        // handle new order
        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged  = $this->merger->merge($sheet->getNotCancelledOrders());
            $selectedPlan = $orderMerged->getPlan();
        }

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getIncludedProduct($product);

            if ($includedProduct) {
                $remainingQuantity = $product->getQuantityMax() - $includedProduct->getQuantity();
            }
        }

        $max = min(
            $remainingQuantity,
            $product->getQuantityMax(),
            $product->getAvailability()
        );

        return $max < 0 ? 0 : $max;
    }
}
