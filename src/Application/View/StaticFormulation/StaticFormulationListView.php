<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\StaticFormulation;

class StaticFormulationListView
{
    /** @var StaticFormulationView[] */
    public $staticFormulationViews;

    public function __construct(array $staticFormulationViews = [])
    {
        $this->staticFormulationViews = $staticFormulationViews;
    }
}
