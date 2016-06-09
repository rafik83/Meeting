<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Application\View\Package\PackageView;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class PackageViewQueryHandler
{
    /**
     * @var PlansViewQueryHandler
     */
    private $plansViewQueryHandler;

    /**
     * @param PlansViewQueryHandler $plansViewQueryHandler
     */
    public function __construct(PlansViewQueryHandler $plansViewQueryHandler)
    {
        $this->plansViewQueryHandler = $plansViewQueryHandler;
    }

    /**
     * @param PackageViewQuery $packageViewQuery
     * @return PackageView
     * @throws \Exception
     */
    public function handle(PackageViewQuery $packageViewQuery)
    {
        if ($packageViewQuery->currentStep->type === Step::TYPE_PLAN) {
            $packageViewProducts = $this->plansViewQueryHandler->handle(
                new PlansViewQuery(
                    $packageViewQuery->event,
                    $packageViewQuery->package,
                    $packageViewQuery->locale
                )
            );
        } else {
            throw new \Exception(sprintf('Step type %s not implemented', $packageViewQuery->currentStep->type));
        }

        return new PackageView(
            $packageViewProducts,
            $packageViewQuery->funnel,
            $packageViewQuery->currentStep
        );
    }
}
