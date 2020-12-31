<?php

namespace Proximum\Vimeet\Domain\Event\Catalog\External;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
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
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage)
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event->getDuplicatedFrom());

        if (null !== $catalogVisibility) {
            $newCatalogVisibility = new CatalogVisibility($event);
            $types = [];
            $categories = [];

            foreach ($catalogVisibility->getTypes() as $type) {
                if (isset($duplicatorDataStorage->types[$type->getId()])) {
                    $types[] = $duplicatorDataStorage->types[$type->getId()];
                }
            }

            foreach ($catalogVisibility->getCategories() as $category) {
                if (isset($duplicatorDataStorage->categories[$category->getId()])) {
                    $categories[] = $duplicatorDataStorage->categories[$category->getId()];
                }
            }

            $newCatalogVisibility->updateTypesAndCategories($types, $categories);
            $this->catalogVisibilityRepository->add($newCatalogVisibility);

            $event->setExternalCatalog($event->getDuplicatedFrom()->isExternalCatalogEnabled());
            $this->eventRepository->set($event);
        }
    }
}
