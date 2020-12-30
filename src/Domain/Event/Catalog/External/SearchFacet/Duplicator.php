<?php

namespace Proximum\Vimeet\Domain\Event\Catalog\External\SearchFacet;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class Duplicator
{
    /**
     * @var SearchFacetRepositoryInterface
     */
    private $searchFacetRepository;

    /**
     * @param SearchFacetRepositoryInterface $searchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $searchFacets = $this->searchFacetRepository->getByEvent($event->getDuplicatedFrom());

        foreach ($searchFacets as $searchFacet) {
            $newSearchFacet = new SearchFacet(
                $event,
                $searchFacet->getType(),
                $searchFacet->isEnabled()
            );

            foreach ($event->getLocales() as $locale) {
                $newSearchFacet->translate(
                    $locale,
                    $searchFacet->getLabel($locale),
                    $searchFacet->getPlaceholder($locale)
                );
            }

            $this->searchFacetRepository->add($newSearchFacet);
        }
    }
}
