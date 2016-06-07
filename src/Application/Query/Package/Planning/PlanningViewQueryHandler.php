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
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class PlanningViewQueryHandler
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
    }

    /**
     * @param PlanningViewQuery $planningViewQuery
     *
     * @return PlanningView
     */
    public function handle(PlanningViewQuery $planningViewQuery)
    {
        $locale          = $planningViewQuery->locale;
        $planningProduct = $planningViewQuery->sheet->getPackage()->getPlanning();
        $selectedPlan    = $this->cartRowRepository->findCartRowPlanBySheet($planningViewQuery->sheet);
        $numberIncluded  = 0;

        if (null !== $selectedPlan) {
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
