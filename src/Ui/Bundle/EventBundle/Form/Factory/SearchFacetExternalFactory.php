<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Factory;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Query\Catalog\FilteredFieldsQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchExternalType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class SearchFacetExternalFactory
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var CommandBus */
    private $commandBus;

    /** @var FormFactory */
    private $formFactory;

    /** @var array of TypeView[] indexed by Event id */
    private $typeViewsByEvent;

    /**
     * SearchFacetExternalFactory constructor.
     *
     * @param CommandBus                           $commandBus
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param FormFactory                          $formFactory
     */
    public function __construct(
        CommandBus $commandBus,
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        FormFactory $formFactory
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->commandBus                  = $commandBus;
        $this->formFactory                 = $formFactory;
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     *
     * @return FormInterface
     * @throws CatalogVisibilityNotFoundException
     */
    public function create(Event $event, string $locale, array $filters): FormInterface
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if ($catalogVisibility === null) {
            throw new CatalogVisibilityNotFoundException();
        }

        $typeViews = $this->getTypeViews($event, $locale);

        $organizationCategoryViews = $this->commandBus->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $positionViews = $this->commandBus->handle(
            new PositionViewQuery($event, $locale)
        );

        return $this->getForm($event, $locale, $filters, $typeViews, $organizationCategoryViews, $positionViews);
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param array  $currentAggregations
     *
     * @return FormInterface
     * @throws CatalogVisibilityNotFoundException
     */
    public function createFiltered(
        Event $event,
        string $locale,
        array $filters,
        array $currentAggregations
    ): FormInterface {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if ($catalogVisibility === null) {
            throw new CatalogVisibilityNotFoundException();
        }

        $typeViews = $this->getTypeViews($event, $locale);

        $organizationCategoryViews = $this->commandBus->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $positionViews = $this->commandBus->handle(
            new PositionViewQuery($event, $locale)
        );

        /** @var FilteredFieldsView $filteredFieldsView */
        $filteredFieldsView = $this->commandBus->handle(
            new FilteredFieldsQuery(
                $event,
                $filters,
                $currentAggregations,
                $typeViews,
                $organizationCategoryViews,
                $positionViews,
                $locale
            )
        );

        return $this->getForm(
            $event,
            $locale,
            $filters,
            $filteredFieldsView->typeViews,
            $filteredFieldsView->organizationCategoryViews,
            $filteredFieldsView->positionViews
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return TypeView[]
     * @throws CatalogVisibilityNotFoundException
     */
    public function getTypeViews(Event $event, string $locale): array
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if ($catalogVisibility === null) {
            throw new CatalogVisibilityNotFoundException();
        }

        if (isset($this->typeViewsByEvent[$event->getId()])) {
            return $this->typeViewsByEvent[$event->getId()];
        }

        $this->typeViewsByEvent[$event->getId()] = $this->commandBus->handle(
            new TypeViewQuery($event, $catalogVisibility->getTypes(), $locale)
        );

        return $this->typeViewsByEvent[$event->getId()];
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param array  $typeViews
     * @param array  $organizationCategoryViews
     * @param array  $positionViews
     *
     * @return FormInterface
     */
    private function getForm(
        Event $event,
        string $locale,
        array $filters = [],
        array $typeViews,
        array $organizationCategoryViews,
        array $positionViews
    ): FormInterface {
        return $this->formFactory->createNamed('', SearchExternalType::class, $filters, [
            'typeViews'                 => $typeViews,
            'organizationCategoryViews' => $organizationCategoryViews,
            'positionViews'             => $positionViews,
            'event'                     => $event,
            'locale'                    => $locale,
        ]);
    }
}
