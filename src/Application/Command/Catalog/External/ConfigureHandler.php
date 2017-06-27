<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class ConfigureHandler
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /**
     * ConfigureHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param SearchFacetRepositoryInterface       $searchFacetRepository
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        SearchFacetRepositoryInterface $searchFacetRepository
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->searchFacetRepository       = $searchFacetRepository;
    }

    /**
     * @param Configure $command
     */
    public function handle(Configure $command)
    {
        $catalogVisibility = new CatalogVisibility(
            $command->event,
            $command->types,
            $command->categories
        );

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

        $this->catalogVisibilityRepository->add($catalogVisibility);
    }
}
