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
    public function getMaxPlanning(Sheet $sheet): int
    {
        $planning = $sheet->getPackage()->getPlanning();

        if (!$planning) {
            return 0;
        }

        $countParticipants = $sheet->countParticipants();
        $remainingQuantity = $countParticipants;

        $selectedPlan = $this->getSelectedPlan($sheet);

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
     * @return float int or INF
     */
    public function getMaxByProduct(Sheet $sheet, Product $product): float
    {
        if ($product->isAttributable()) {
            return min(
                $sheet->countParticipants(),
                $product->getQuantityMax(),
                $product->getAvailability()
            );
        }

        $remainingQuantity = INF;
        $selectedPlan = $this->getSelectedPlan($sheet);

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getIncludedProduct($product);

            if ($includedProduct) {
                $remainingQuantity = $product->getQuantityMax() - $includedProduct->getQuantity();
            }
        }

        return max(
            0,
            min(
                $remainingQuantity,
                $product->getQuantityMax(),
                $product->getAvailability()
            )
        );
    }

    private function getSelectedPlan(Sheet $sheet): ?Product
    {
        $cart = $this->cartManager->getCart($sheet);

        if (null !== $cart->getPlanRow()) {
            return $cart->getPlanRow()->getProduct();
        }

        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->merger->merge($sheet->getNotCancelledOrders());

            return $orderMerged->getPlan();
        }

        return null;
    }
}
