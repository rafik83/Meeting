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
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureHandler
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * ConfigureHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param SearchFacetRepositoryInterface       $searchFacetRepository
     * @param EventRepositoryInterface             $eventRepository
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        SearchFacetRepositoryInterface $searchFacetRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->searchFacetRepository       = $searchFacetRepository;
        $this->eventRepository             = $eventRepository;
    }

    /**
     * @param Configure $command
     */
    public function handle(Configure $command)
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($command->event);

        if ($catalogVisibility === null) {
            $catalogVisibility = new CatalogVisibility($command->event);
        }

        // Set bool state to event
        $command->event->setExternalCatalog($command->externalCatalogEnabled);

        // Update CatalogVisibility types and categories
        $catalogVisibility->updateTypesAndCategories($command->types, $command->categories);

        // Set CatalogVisibility
        $this->catalogVisibilityRepository->set($catalogVisibility);

        // Update or Add SearchFacet and SearchFacetTranslations for CatalogVisibility
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

        // Flush event
        $this->eventRepository->set($command->event);
    }
}
