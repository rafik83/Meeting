<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Planning;

use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;

class PlanningViewQueryHandler
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
     * @param PlanningViewQuery $planningViewQuery
     *
     * @return ProductView
     */
    public function handle(PlanningViewQuery $planningViewQuery)
    {
        $cart            = $this->cartManager->getCart($planningViewQuery->sheet);
        $locale          = $planningViewQuery->locale;
        $planningProduct = $planningViewQuery->sheet->getPackage()->getPlanning();
        $selectedPlan    = $cart->getPlanRow();
        $included        = 0;

        if ($selectedPlan) {
            $planningProductIncluded = $selectedPlan->getProduct()->getIncludedPlanningProduct();

            if ($planningProductIncluded) {
                $included = $planningProductIncluded->getQuantity();
            }
        }

        return new ProductView(
            $planningProduct->getId(),
            $planningProduct->getTitle($locale),
            $planningProduct->getUnitPrice(),
            $planningProduct->getHeading($locale),
            $planningProduct->getDescription($locale),
            $planningProduct->getAddon($locale),
            $planningProduct->getImage(),
            $planningProduct->getAvailabilityCurrent(),
            $planningProduct->getAvailabilityMax(),
            $planningProduct->isOutOfStock(),
            $planningProduct->getVatMode(),
            $planningProduct->getEvent()->getCurrency(),
            $included
        );
    }
}
