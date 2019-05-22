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
use Proximum\Vimeet\Application\Query\Catalog\NomenclatureTagViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Domain\Catalog\CanDisplayObjectiveFilter;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class CatalogFilterViewsHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var VisibleParticipationCategories */
    private $visibleParticipationCategories;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /** @var EngineInterface */
    private $engine;

    /** @var CanDisplayObjectiveFilter */
    private $canDisplayObjectiveFilter;

    /**
     * @param QueryBusInterface              $queryBus
     * @param VisibleParticipationCategories $visibleParticipationCategories
     * @param VisibleParticipationTypes      $visibleParticipationTypes
     * @param EngineInterface                $engine
     * @param CanDisplayObjectiveFilter      $canDisplayObjectiveFilter
     */
    public function __construct(
        QueryBusInterface $queryBus,
        VisibleParticipationCategories $visibleParticipationCategories,
        VisibleParticipationTypes $visibleParticipationTypes,
        EngineInterface $engine,
        CanDisplayObjectiveFilter $canDisplayObjectiveFilter
    ) {
        $this->queryBus = $queryBus;
        $this->visibleParticipationCategories = $visibleParticipationCategories;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
        $this->engine = $engine;
        $this->canDisplayObjectiveFilter     = $canDisplayObjectiveFilter;
    }

    /**
     * @param CatalogFilterViews $catalogFilterViews
     *
     * @return CatalogFilterViewsResult
     */
    public function handle(
        CatalogFilterViews $catalogFilterViews
    ): CatalogFilterViewsResult {
        $event = $catalogFilterViews->event;
        $sheet = $catalogFilterViews->sheet;
        $locale = $catalogFilterViews->locale;

        /** @var SearchFacetsView $searchFacetsView */
        $searchFacetsView = $this->queryBus->handle(
            new SearchFacetViewQuery($event, $locale)
        );

        $categoryViews = [];
        $typeViews = [];
        $organizationCategoryViews = [];
        $positionViews = [];
        $taggedNomenclatureTagViews = [];

        if ($searchFacetsView->hasCategory()) {
            $visibleCategories = $this
                ->visibleParticipationCategories
                ->getAllowedCategoriesList($sheet);

            if (empty($visibleCategories)) {
                return new CatalogFilterViewsResult(
                    CatalogFilterViewsResult::EMPTY_CATEGORY_OR_TYPE,
                    $categoryViews,
                    $typeViews,
                    $organizationCategoryViews,
                    $positionViews,
                    $taggedNomenclatureTagViews,
                    $this->engine->renderResponse(
                        'EventBundle:Catalog:no-visible-category.html.twig',
                        ['event' => $event, 'sheet' => $sheet]
                    ),
                    $this->canDisplayObjectiveFilter->isSatisfiedBy($sheet, $locale)
                );
            }

            $categoryViews = $this->queryBus->handle(new CategoryViewQuery($event, $visibleCategories, $locale));
        } else {
            $visibleTypes = $this->visibleParticipationTypes->getAllowedTypesList($sheet);

            if (empty($visibleTypes)) {
                return new CatalogFilterViewsResult(
                    CatalogFilterViewsResult::EMPTY_CATEGORY_OR_TYPE,
                    $categoryViews,
                    $typeViews,
                    $organizationCategoryViews,
                    $positionViews,
                    $taggedNomenclatureTagViews,
                    $this->engine->renderResponse(
                        'EventBundle:Catalog:no-visible-type.html.twig',
                        ['event' => $event, 'sheet' => $sheet]
                    ),
                    $this->canDisplayObjectiveFilter->isSatisfiedBy($sheet, $locale)
                );
            }

            $typeViews = $this->queryBus->handle(new TypeViewQuery($event, $visibleTypes, $locale));
        }

        if (null !== $searchFacetsView->getOrganizationCategory()) {
            $organizationCategoryViews = $this->queryBus->handle(new OrganizationCategoryViewQuery($event, $locale));
        }

        if (null !== $searchFacetsView->getPosition()) {
            $positionViews = $this->queryBus->handle(new PositionViewQuery($event, $locale));
        }

        $tagFilterViews = $searchFacetsView->getTagFilterViews();

        if (!empty($tagFilterViews)) {
            $taggedNomenclatureTagViews = $this->queryBus->handle(
                new NomenclatureTagViewQuery($event, array_keys($tagFilterViews), $locale)
            );
        }

        return new CatalogFilterViewsResult(
            CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
            $categoryViews,
            $typeViews,
            $organizationCategoryViews,
            $positionViews,
            $taggedNomenclatureTagViews,
            null,
            $this->canDisplayObjectiveFilter->isSatisfiedBy($sheet, $locale)
        );
    }
}
