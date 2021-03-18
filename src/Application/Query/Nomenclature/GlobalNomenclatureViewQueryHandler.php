<?php

namespace Proximum\Vimeet\Application\Query\Nomenclature;

use Proximum\Vimeet\Application\View\Nomenclature\GlobalNomenclatureView;

class GlobalNomenclatureViewQueryHandler extends AbstractNomenclatureViewQueryHandler
{
    /**
     * @param GlobalNomenclatureViewQuery $query
     *
     * @return GlobalNomenclatureView[]
     */
    public function handle(GlobalNomenclatureViewQuery $query): array
    {
        $nomenclatures = $this->nomenclatureRepository->findGlobals();
        $nomenclatureViews = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureViews[] = new GlobalNomenclatureView(
                $nomenclature->getId(),
                $nomenclature->getTitle(),
                $nomenclature->getDepth(),
                $this->removeAuthorizationChecker->canBeRemoved($nomenclature)
            );
        }

        return $nomenclatureViews;
    }
}
