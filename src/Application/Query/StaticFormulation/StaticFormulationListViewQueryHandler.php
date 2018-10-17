<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\StaticFormulation;

use Proximum\Vimeet\Application\View\StaticFormulation\StaticFormulationListView;

class StaticFormulationListViewQueryHandler
{
    public function __construct()
    {
    }

    public function handle(StaticFormulationListViewQuery $query): StaticFormulationListView
    {
        return new StaticFormulationListView();
    }
}
