<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class PackageViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Step
     */
    public $currentStep;

    /**
     * @var Funnel
     */
    public $funnel;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Funnel $funnel
     * @param Step   $currentStep
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Funnel $funnel, Step $currentStep, Sheet $sheet, $locale)
    {
        $this->funnel      = $funnel;
        $this->currentStep = $currentStep;
        $this->sheet       = $sheet;
        $this->locale      = $locale;
    }
}
