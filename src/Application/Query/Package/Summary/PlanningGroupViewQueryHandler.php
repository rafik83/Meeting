<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PlanningGroupView;

class PlanningGroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
    }

    /**
     * @param PlanningGroupViewQuery $planningGroupViewQuery
     *
     * @return PlanningGroupView
     * @throws \Exception
     */
    public function handle(PlanningGroupViewQuery $planningGroupViewQuery)
    {
        $cart    = $planningGroupViewQuery->cart;
        $package = $planningGroupViewQuery->sheet->getPackage();

        if (!$package->isParticipantAndPlanningEnabled()) {
            throw new \Exception('Planning is not enabled');
        }

        $planningRow  = $cart->getPlanningRow();
        $planningView = null;

        if (null !== $planningRow) {
            $planningView = $this->productViewQueryHandler->handle(new ProductViewQuery(
                $planningGroupViewQuery->sheet,
                $planningRow->getProduct(),
                $cart,
                $planningGroupViewQuery->locale,
                $planningGroupViewQuery->planGroupView
            ));
        }

        return new PlanningGroupView(
            $package->getPlanning()->getTitle($planningGroupViewQuery->locale),
            [$planningView],
            null !== $planningView ? $planningView->total : 0
        );
    }
}
