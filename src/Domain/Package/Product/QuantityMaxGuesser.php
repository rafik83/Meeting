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
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
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
        if (null === $sheet->getPackage()->getPlanning()) {
            return 0;
        }

        $planningQuantityMax = 0;

        if ($sheet->getPackage()->getPlanning()) {
            $planningQuantityMax = $sheet->getPackage()->getPlanning()->getQuantityMax();
        }

        $cart         = $this->cartManager->getCart($sheet);
        $selectedPlan = $cart->getPlanRow();

        if (!$selectedPlan) {
            return $planningQuantityMax;
        }

        $included  = 0;
        $includedPlanningProduct = $selectedPlan->getProduct()->getIncludedPlanningProduct();

        if ($includedPlanningProduct) {
            $included = $includedPlanningProduct->getQuantity();
        }

        $max = min(
            $sheet->getParticipants()->count() - $included,
            $planningQuantityMax
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
        $max          = $product->getQuantityMax();
        $cart         = $this->cartManager->getCart($sheet);
        $selectedPlan = $cart->getPlanRow();

        if (!$selectedPlan) {
            return $max;
        }

        $includedProduct = $selectedPlan->getProduct()->getIncludedProduct($product);

        if (!$includedProduct) {
            return $max;
        }

        $max = $max - $includedProduct->getQuantity();

        return $max < 0 ? 0 : $max;
    }
}
