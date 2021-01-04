<?php

namespace Proximum\Vimeet\Application\Query\Nomenclature;

use Proximum\Vimeet\Application\View\Nomenclature\EventNomenclatureView;

class EventNomenclatureViewQueryHandler extends AbstractNomenclatureViewQueryHandler
{
    /**
     * @param EventNomenclatureViewQuery $query
     *
     * @return EventNomenclatureView[]
     */
    public function handle(EventNomenclatureViewQuery $query): array
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($query->event);
        $nomenclatureViews = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureViews[] = new EventNomenclatureView(
                $nomenclature->getId(),
                $nomenclature->getTitle(),
                $nomenclature->getDepth(),
                $this->removeAuthorizationChecker->canBeRemoved($nomenclature),
                $query->event
            );
        }

        return $nomenclatureViews;
    }
}
