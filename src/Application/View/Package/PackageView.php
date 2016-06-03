<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class PackageView
{
    /**
     * @var Step
     */
    public $currentStep;

    /**
     * @var Funnel
     */
    public $funnel;

    /**
     * @var AbstractProductsView
     */
    public $products;

    /**
     * @param AbstractProductsView $productsView
     * @param Funnel               $funnel
     * @param Step                 $currentStep
     */
    public function __construct(AbstractProductsView $productsView, Funnel $funnel, Step $currentStep)
    {
        $this->products    = $productsView;
        $this->funnel      = $funnel;
        $this->currentStep = $currentStep;
    }
}
