<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Planning;

use Proximum\Vimeet\Application\View\Package\PlanningView;
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
     * @return PlanningView
     */
    public function handle(PlanningViewQuery $planningViewQuery)
    {
        $cart            = $this->cartManager->getCart($planningViewQuery->sheet);
        $locale          = $planningViewQuery->locale;
        $planningProduct = $planningViewQuery->sheet->getPackage()->getPlanning();
        $selectedPlan    = $cart->getPlanRow();
        $numberIncluded  = 0;

        if ($selectedPlan) {
            $planningProductIncluded = $selectedPlan->getProduct()->getIncludedPlanningProduct();
            
            if ($planningProductIncluded) {
                $numberIncluded = $planningProductIncluded->getQuantity();
            }
        }

        return new PlanningView(
            $planningProduct->getId(),
            $planningProduct->getTitle($locale),
            $planningProduct->getDescription($locale),
            $planningProduct->getUnitPrice(),
            $planningProduct->getVatMode(),
            $planningProduct->getQuantityMax(),
            $numberIncluded
        );
    }
}
