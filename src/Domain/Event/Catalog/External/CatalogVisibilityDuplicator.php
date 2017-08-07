<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class CatalogVisibilityDuplicator
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param EventRepositoryInterface             $eventRepository
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Event $event
     * @param array $duplicationHelper
     */
    public function duplicate(Event $event, array $duplicationHelper)
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event->getDuplicatedFrom());

        $newCatalogVisibility = new CatalogVisibility($event);
        $types = [];
        $categories = [];

        foreach ($catalogVisibility->getTypes() as $type) {
            $types[] = $duplicationHelper['type'][$type->getId()];
        }

        foreach ($catalogVisibility->getCategories() as $category) {
            $categories[] = $duplicationHelper['category'][$category->getId()];
        }

        $newCatalogVisibility->updateTypesAndCategories($types, $categories);
        $this->catalogVisibilityRepository->add($newCatalogVisibility);

        $event->setExternalCatalog($event->getDuplicatedFrom()->isExternalCatalogEnabled());
        $this->eventRepository->set($event);
    }
}
