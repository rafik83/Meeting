<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Factory;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\FilteredFieldsQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Domain\Exception\Catalog\CatalogVisibilityNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchExternalType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SearchFacetExternalFactory
{
    /** @var CatalogVisibilityRepositoryInterface */
    private $catalogVisibilityRepository;

    /** @var CommandBus */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var array of TypeView[] indexed by Event id */
    private $typeViewsByEvent;

    /** @var CategoryView[] indexed by Event id */
    private $categoryViewsByEvent;

    /**
     * SearchFacetExternalFactory constructor.
     *
     * @param CommandBus                           $commandBus
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param FormFactoryInterface                 $formFactory
     * @param RouterInterface                      $router
     */
    public function __construct(
        CommandBus $commandBus,
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
        $this->commandBus                  = $commandBus;
        $this->formFactory                 = $formFactory;
        $this->router                      = $router;
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return FormInterface
     */
    public function create(Event $event, string $locale, array $filters): FormInterface
    {
        $initialFieldsView = $this->getInitialFieldsView($event, $locale);

        return $this->getForm(
            $event,
            $locale,
            $filters,
            $initialFieldsView->typeViews,
            $initialFieldsView->categoryViews,
            $initialFieldsView->organizationCategoryViews,
            $initialFieldsView->positionViews
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param array  $currentAggregations
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return FormInterface
     */
    public function createFiltered(
        Event $event,
        string $locale,
        array $filters,
        array $currentAggregations
    ): FormInterface {
        $initialFieldsView = $this->getInitialFieldsView($event, $locale);

        /** @var FilteredFieldsView $filteredFieldsView */
        $filteredFieldsView = $this->commandBus->handle(
            new FilteredFieldsQuery(
                $event,
                $filters,
                $currentAggregations,
                $initialFieldsView->typeViews,
                $initialFieldsView->categoryViews,
                $initialFieldsView->organizationCategoryViews,
                $initialFieldsView->positionViews,
                $locale
            )
        );

        return $this->getForm(
            $event,
            $locale,
            $filters,
            $filteredFieldsView->typeViews,
            $filteredFieldsView->categoryViews,
            $filteredFieldsView->organizationCategoryViews,
            $filteredFieldsView->positionViews
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return TypeView[]
     */
    public function getTypeViews(Event $event, string $locale): array
    {
        if (!isset($this->typeViewsByEvent[$event->getId()])) {
            $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

            if (null === $catalogVisibility) {
                throw new CatalogVisibilityNotFoundException();
            }

            $this->typeViewsByEvent[$event->getId()] = $this->commandBus->handle(
                new TypeViewQuery($event, $catalogVisibility->getTypes(), $locale)
            );
        }

        return $this->typeViewsByEvent[$event->getId()];
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return CategoryView[]
     */
    public function getCategoryViews(Event $event, string $locale): array
    {
        if (!isset($this->categoryViewsByEvent[$event->getId()])) {
            $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

            if (null === $catalogVisibility) {
                throw new CatalogVisibilityNotFoundException();
            }

            $this->categoryViewsByEvent[$event->getId()] = $this->commandBus->handle(
                new CategoryViewQuery($event, $catalogVisibility->getCategories(), $locale)
            );
        }

        return $this->categoryViewsByEvent[$event->getId()];
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @throws CatalogVisibilityNotFoundException
     *
     * @return FilteredFieldsView
     */
    private function getInitialFieldsView(Event $event, string $locale): FilteredFieldsView
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        if (null === $catalogVisibility) {
            throw new CatalogVisibilityNotFoundException();
        }

        $typeViews     = $this->getTypeViews($event, $locale);
        $categoryViews = $this->getCategoryViews($event, $locale);

        $organizationCategoryViews = $this->commandBus->handle(
            new OrganizationCategoryViewQuery($event, $locale)
        );

        $positionViews = $this->commandBus->handle(
            new PositionViewQuery($event, $locale)
        );

        return new FilteredFieldsView(
            $typeViews,
            $organizationCategoryViews,
            $positionViews,
            $categoryViews
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param array  $typeViews
     * @param array  $categoryViews
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
        array $categoryViews,
        array $organizationCategoryViews,
        array $positionViews
    ): FormInterface {
        return $this->formFactory->createNamed('', SearchExternalType::class, $filters, [
            'action'                    => $this->router->generate('event_catalog_external_index'),
            'typeViews'                 => $typeViews,
            'categoryViews'             => $categoryViews,
            'organizationCategoryViews' => $organizationCategoryViews,
            'positionViews'             => $positionViews,
            'event'                     => $event,
            'locale'                    => $locale,
        ]);
    }
}
