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
        $command->event->setExternalCatalog($command->externalCatalogEnabled);

        $command->catalogVisibility->updateTypesAndCategories($command->types, $command->categories);

        if (null !== $this->catalogVisibilityRepository->getByEvent($command->event)) {
            $this->catalogVisibilityRepository->set($command->catalogVisibility);
        } else {
            $this->catalogVisibilityRepository->add($command->catalogVisibility);
        }

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

        $this->eventRepository->set($command->event);
    }
}
