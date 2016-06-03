<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class PackageViewQuery
{
    /**
     * @var Package
     */
    public $package;

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
     * @var Event
     */
    public $event;

    /**
     * @param Funnel  $funnel
     * @param Step    $currentStep
     * @param Event   $event
     * @param Package $package
     * @param string  $locale
     */
    public function __construct(Funnel $funnel, Step $currentStep, Event $event, Package $package, $locale)
    {
        $this->funnel      = $funnel;
        $this->currentStep = $currentStep;
        $this->event       = $event;
        $this->package     = $package;
        $this->locale      = $locale;
    }
}
