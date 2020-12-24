<?php

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
