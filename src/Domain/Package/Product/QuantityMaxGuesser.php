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

class QuantityMaxGuesser
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
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
        $selectedPlan      = $cart->getPlanRow();
        $remainingQuantity = INF;

        if ($selectedPlan) {
            $includedPlanningProduct = $selectedPlan->getProduct()->getIncludedPlanningProduct();

            if ($includedPlanningProduct) {
                $remainingQuantity = $sheet->getParticipants()->count() - $includedPlanningProduct->getQuantity();
            }
        }

        $max = min(
            $remainingQuantity,
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
        $cart              = $this->cartManager->getCart($sheet);
        $selectedPlan      = $cart->getPlanRow();
        $remainingQuantity = INF;

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getProduct()->getIncludedProduct($product);

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
