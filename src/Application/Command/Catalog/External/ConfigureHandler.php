<?php

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Application\Command\Catalog\CatalogTagFilterHandler;
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

    /** @var CatalogTagFilterHandler */
    private $catalogTagFilterHandler;

    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        EventRepositoryInterface $eventRepository,
        SetSearchFacetHandler $setSearchFacetHandler,
        CatalogTagFilterHandler $catalogTagFilterHandler
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->eventRepository             = $eventRepository;
        $this->setSearchFacetHandler       = $setSearchFacetHandler;
        $this->catalogTagFilterHandler     = $catalogTagFilterHandler;
    }

    /**
     * @param Configure $command
     */
    public function handle(Configure $command)
    {
        $this->handleConfigure($command);
        $this->catalogTagFilterHandler->handle($command);
    }

    private function handleConfigure(Configure $command): void
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
