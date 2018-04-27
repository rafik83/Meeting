<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class UpdateHandler
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
     * @param Update $update
     */
    public function handle(Update $update)
    {
        foreach ($update->searchFacets as $type => $searchFacet) {
            $found = false;

            foreach ($update->persistedSearchFacets as $persistedSearchFacet) {
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
                $newSearchFacet = new SearchFacet($update->event, $type, $searchFacet['enabled']);

                foreach ($searchFacet['translations'] as $locale => $translation) {
                    $newSearchFacet->translate($locale, $translation['label'], $translation['placeholder']);
                }

                $this->searchFacetRepository->add($newSearchFacet);
            }
        }
    }
}
