<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Catalog\CategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class CategoryTypeOrganizationAndPositionViewsHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var VisibleParticipationCategories */
    private $visibleParticipationCategories;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param QueryBusInterface              $queryBus
     * @param VisibleParticipationCategories $visibleParticipationCategories
     * @param VisibleParticipationTypes      $visibleParticipationTypes
     * @param EngineInterface                $engine
     */
    public function __construct(
        QueryBusInterface $queryBus,
        VisibleParticipationCategories $visibleParticipationCategories,
        VisibleParticipationTypes $visibleParticipationTypes,
        EngineInterface $engine
    ) {
        $this->queryBus = $queryBus;
        $this->visibleParticipationCategories = $visibleParticipationCategories;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
        $this->engine = $engine;
    }

    /**
     * @param CategoryTypeOrganizationAndPositionViews $categoryTypeOrganizationAndPositionViews
     *
     * @return CategoryTypeOrganizationAndPositionViewsResult
     */
    public function handle(
        CategoryTypeOrganizationAndPositionViews $categoryTypeOrganizationAndPositionViews
    ): CategoryTypeOrganizationAndPositionViewsResult {
        $event = $categoryTypeOrganizationAndPositionViews->event;
        $sheet = $categoryTypeOrganizationAndPositionViews->sheet;
        $locale = $categoryTypeOrganizationAndPositionViews->locale;

        $searchFacetsView = $this->queryBus->handle(
            new SearchFacetViewQuery($event, $locale)
        );

        $categoryViews = [];
        $typeViews = [];
        $organizationCategoryViews = [];
        $positionViews = [];

        if ($searchFacetsView->hasCategory()) {
            $visibleCategories = $this
                ->visibleParticipationCategories
                ->getAllowedCategoriesList($sheet);

            if (empty($visibleCategories)) {
                return new CategoryTypeOrganizationAndPositionViewsResult(
                    CategoryTypeOrganizationAndPositionViewsResult::EMPTY_CATEGORY_OR_TYPE,
                    $categoryViews,
                    $typeViews,
                    $organizationCategoryViews,
                    $positionViews,
                    $this->engine->renderResponse(
                        'EventBundle:Catalog:no-visible-category.html.twig',
                        ['event' => $event, 'sheet' => $sheet]
                    )
                );
            }

            $categoryViews = $this->queryBus->handle(new CategoryViewQuery($event, $visibleCategories, $locale));
        } else {
            $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($sheet);

            if (empty($visibleTypes)) {
                return new CategoryTypeOrganizationAndPositionViewsResult(
                    CategoryTypeOrganizationAndPositionViewsResult::EMPTY_CATEGORY_OR_TYPE,
                    $categoryViews,
                    $typeViews,
                    $organizationCategoryViews,
                    $positionViews,
                    $this->engine->renderResponse(
                        'EventBundle:Catalog:no-visible-type.html.twig',
                        ['event' => $event, 'sheet' => $sheet]
                    )
                );
            }

            $typeViews = $this->queryBus->handle(new TypeViewQuery($event, $visibleTypes, $locale));
        }

        $organizationCategoryViews = $this->queryBus->handle(new OrganizationCategoryViewQuery($event, $locale));
        $positionViews = $this->queryBus->handle(new PositionViewQuery($event, $locale));

        return new CategoryTypeOrganizationAndPositionViewsResult(
            CategoryTypeOrganizationAndPositionViewsResult::RESULT_CATEGORY_OR_TYPE,
            $categoryViews,
            $typeViews,
            $organizationCategoryViews,
            $positionViews
        );
    }
}
