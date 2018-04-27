<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Plan;

use Proximum\Vimeet\Application\View\Package\PlansView;

class PlansViewQueryHandler
{
    /**
     * @var PlanViewQueryHandler
     */
    private $planViewQueryHandler;

    /**
     * @param PlanViewQueryHandler $planViewQueryHandler
     */
    public function __construct(PlanViewQueryHandler $planViewQueryHandler)
    {
        $this->planViewQueryHandler = $planViewQueryHandler;
    }

    /**
     * @param PlansViewQuery $plansViewQuery
     *
     * @return PlansView
     */
    public function handle(PlansViewQuery $plansViewQuery)
    {
        $plansView = new PlansView();

        foreach ($plansViewQuery->package->getPlans() as $plan) {
            $plansView->plans[] = $this->planViewQueryHandler->handle(
                new PlanViewQuery(
                    $plansViewQuery->event,
                    $plan,
                    $plansViewQuery->locale
                )
            );
        }

        return $plansView;
    }
}
