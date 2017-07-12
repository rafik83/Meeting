<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SetSearchFacetHandler
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /**
     * SetSearchFacetHandler constructor.
     *
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
        foreach ($command->searchFacets as $searchFacet) {
            foreach ($searchFacet->getTranslations() as $locale => $translation) {
                $searchFacet->translate($locale, $translation->getLabel(), $translation->getPlaceholder());
            }

            if (null === $searchFacet->getId()) {
                $this->searchFacetRepository->add($searchFacet);
            } else {
                $this->searchFacetRepository->set($searchFacet);
            }
        }
    }
}
