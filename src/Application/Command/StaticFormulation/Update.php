<?php

namespace Proximum\Vimeet\Application\Command\StaticFormulation;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;

class Update implements Command
{
    /** @var StaticFormulation */
    public $staticFormulation;

    /** @var array */
    public $translations;

    /** @var Type[] */
    public $types;

    public function __construct(StaticFormulation $staticFormulation)
    {
        $this->staticFormulation = $staticFormulation;

        $this->types = $staticFormulation->getTypes();
        $this->translations = [];

        foreach ($staticFormulation->getTranslations() as $staticFormulationTranslation) {
            $this->translations[$staticFormulationTranslation->getLocale()] = [
                'title' => $staticFormulationTranslation->getTitle(),
            ];
        }
    }
}
