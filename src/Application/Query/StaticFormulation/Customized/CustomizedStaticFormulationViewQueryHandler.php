<?php

namespace Proximum\Vimeet\Application\Query\StaticFormulation\Customized;

use Proximum\Vimeet\Application\View\StaticFormulation\Customized\CustomizedStaticFormulationView;
use Proximum\Vimeet\Domain\Model\Type;

class CustomizedStaticFormulationViewQueryHandler
{
    public function handle(CustomizedStaticFormulationViewQuery $query): CustomizedStaticFormulationView
    {
        $types = [];

        /** @var Type $type */
        foreach ($query->staticFormulation->getTypes() as $type) {
            $types[$type->getId()] = $type->getTitle($query->locale);
        }

        return new CustomizedStaticFormulationView(
            $query->staticFormulation->getKey(),
            $query->staticFormulation->getId(),
            $query->staticFormulation->getTitle($query->locale),
            $types
        );
    }
}
