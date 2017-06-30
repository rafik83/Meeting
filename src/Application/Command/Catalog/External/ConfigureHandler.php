<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureHandler
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var SetSearchFacetHandler */
    private $setSearchFacetHandler;

    /**
     * ConfigureHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param EventRepositoryInterface             $eventRepository
     * @param SetSearchFacetHandler                $setSearchFacetHandler
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        EventRepositoryInterface $eventRepository,
        SetSearchFacetHandler $setSearchFacetHandler
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->eventRepository             = $eventRepository;
        $this->setSearchFacetHandler       = $setSearchFacetHandler;
    }

    /**
     * @param Configure $command
     */
    public function handle(Configure $command)
    {
        $catalogVisibility = $command->catalogVisibilityView->catalogVisibility;

        $command->event->setExternalCatalog($command->externalCatalogEnabled);

        $catalogVisibility->updateTypesAndCategories($command->types, $command->categories);

        if (null !== $this->catalogVisibilityRepository->getByEvent($command->event)) {
            $this->catalogVisibilityRepository->set($catalogVisibility);
        } else {
            $this->catalogVisibilityRepository->add($catalogVisibility);
        }

        $this->setSearchFacetHandler->handle(new SetSearchFacet($command->searchFacets));

        $this->eventRepository->set($command->event);
    }
}
