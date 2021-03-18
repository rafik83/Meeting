<?php

namespace Proximum\Vimeet\Application\View\StaticFormulation;

use Proximum\Vimeet\Application\View\StaticFormulation\Customized\CustomizedStaticFormulationView;
use Proximum\Vimeet\Application\View\StaticFormulation\Generic\GenericStaticFormulationView;

class StaticFormulationView
{
    /** @var string */
    public $key;

    /** @var GenericStaticFormulationView */
    public $genericStaticFormulationView;

    /** @var CustomizedStaticFormulationView[] */
    public $customizedStaticFormulationViews;

    public function __construct(
        string $key,
        GenericStaticFormulationView $genericStaticFormulationView,
        array $customizedStaticFormulationViews = []
    ) {
        $this->key = $key;
        $this->genericStaticFormulationView = $genericStaticFormulationView;
        $this->customizedStaticFormulationViews = $customizedStaticFormulationViews;
    }

    public function getNumberOfElements(): int
    {
        return \count($this->customizedStaticFormulationViews) + 1;
    }
}
