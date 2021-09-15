<?php

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SetSearchFacetHandler
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /**
     * @param SearchFacetRepositoryInterface $searchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @param SetSearchFacet $command
     */
    public function handle(SetSearchFacet $command)
    {
        foreach ($command->searchFacets as $type => $searchFacet) {
            $found = false;

            foreach ($command->persistedSearchFacets as $persistedSearchFacet) {
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
                $newSearchFacet = new SearchFacet($command->event, $type, $searchFacet['enabled']);

                foreach ($searchFacet['translations'] as $locale => $translation) {
                    $newSearchFacet->translate($locale, $translation['label'], $translation['placeholder']);
                }

                $this->searchFacetRepository->add($newSearchFacet);
            }
        }
    }
}
