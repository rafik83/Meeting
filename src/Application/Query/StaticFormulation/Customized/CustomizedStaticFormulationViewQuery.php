<?php

namespace Proximum\Vimeet\Application\Query\StaticFormulation\Customized;

use Proximum\Vimeet\Domain\Model\StaticFormulation;

class CustomizedStaticFormulationViewQuery
{
    /** @var StaticFormulation */
    public $staticFormulation;

    /** @var string */
    public $locale;

    public function __construct(StaticFormulation $staticFormulation, string $locale)
    {
        $this->staticFormulation = $staticFormulation;
        $this->locale = $locale;
    }
}
