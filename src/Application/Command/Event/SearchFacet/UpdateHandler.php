<?php

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Application\Command\Catalog\CatalogTagFilterHandler;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class UpdateHandler
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var CatalogTagFilterHandler */
    private $catalogTagFilterHandler;

    public function __construct(
        SearchFacetRepositoryInterface $searchFacetRepository,
        CatalogTagFilterHandler $catalogTagFilterHandler
    ) {
        $this->searchFacetRepository = $searchFacetRepository;
        $this->catalogTagFilterHandler = $catalogTagFilterHandler;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $this->handleSearchFacets($update->event, $update->persistedSearchFacets, $update->searchFacets);
        $this->catalogTagFilterHandler->handle($update);
    }

    private function handleSearchFacets(Event $event, array $persistedSearchFacets, array $searchFacets): void
    {
        foreach ($searchFacets as $type => $searchFacet) {
            $found = false;

            foreach ($persistedSearchFacets as $persistedSearchFacet) {
                if ($persistedSearchFacet->getType() === $type) {
                    $found = true;
                    $persistedSearchFacet->setEnabled($searchFacet['enabled']);

                    foreach ($searchFacet['translations'] as $locale => $translation) {
                        $persistedSearchFacet->translate($locale, $translation['label'], $translation['placeholder']);
                    }

                    $this->searchFacetRepository->set($persistedSearchFacet);
                }
            }

            if (false === $found) {
                $newSearchFacet = new SearchFacet($event, $type, $searchFacet['enabled']);

                foreach ($searchFacet['translations'] as $locale => $translation) {
                    $newSearchFacet->translate($locale, $translation['label'], $translation['placeholder']);
                }

                $this->searchFacetRepository->add($newSearchFacet);
            }
        }
    }
}
