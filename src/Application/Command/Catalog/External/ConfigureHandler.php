<?php

/*
 * This file is part of the Proximum Vimeet project.
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
        $catalogVisibility = $command->catalogVisibility;

        $command->event->setExternalCatalog($command->externalCatalogEnabled);

        $catalogVisibility->updateTypesAndCategories($command->types, $command->categories);
        $catalogVisibility->enableMessage($command->hasMessage);
        $catalogVisibility->setRegistrationUrl($command->registrationUrl);

        foreach ($command->messageTranslations as $locale => $message) {
            $catalogVisibility->translate(
                $message['title'] ?? '',
                $message['content'] ?? '',
                $locale
            );
        }

        if (null !== $this->catalogVisibilityRepository->getByEvent($command->event)) {
            $this->catalogVisibilityRepository->set($catalogVisibility);
        } else {
            $this->catalogVisibilityRepository->add($catalogVisibility);
        }

        $this->setSearchFacetHandler->handle(
            new SetSearchFacet($command->event, $command->searchFacets, $command->persistedSearchFacets)
        );

        $this->eventRepository->set($command->event);
    }
}
