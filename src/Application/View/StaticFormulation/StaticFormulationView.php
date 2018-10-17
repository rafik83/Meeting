<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\StaticFormulation;

use Proximum\Vimeet\Application\View\StaticFormulation\Generic\GenericStaticFormulationView;

class StaticFormulationView
{
    /** @var GenericStaticFormulationView */
    private $genericStaticFormulationView;

    /** @var CustomizedStaticFormulationView[] */
    private $customizedStaticFormulationViews;

    public function __construct(
        GenericStaticFormulationView $genericStaticFormulationView,
        array $customizedStaticFormulationViews = []
    ) {
        $this->genericStaticFormulationView = $genericStaticFormulationView;
        $this->customizedStaticFormulationViews = $customizedStaticFormulationViews;
    }
}
