<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Category;

use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQueryHandler;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\View\CategoryView;

class MeetingCategoryViewQueryHandler
{
    /** @var VisibleParticipationCategories */
    private $visibleParticipationCategories;

    /** @var SearchFacetViewQueryHandler */
    private $searchFacetViewQueryHandler;

    /**
     * @param VisibleParticipationCategories $visibleParticipationCategories
     * @param SearchFacetViewQueryHandler    $searchFacetViewQueryHandler
     */
    public function __construct(
        VisibleParticipationCategories $visibleParticipationCategories,
        SearchFacetViewQueryHandler $searchFacetViewQueryHandler
    ) {
        $this->visibleParticipationCategories = $visibleParticipationCategories;
        $this->searchFacetViewQueryHandler = $searchFacetViewQueryHandler;
    }

    /**
     * @param MeetingCategoryViewQuery $query
     *
     * @return CategoryView[]
     */
    public function handle(MeetingCategoryViewQuery $query): array
    {
        $searchFacetViews = $this->searchFacetViewQueryHandler->handle(
            new SearchFacetViewQuery(
                $query->sheet->getEvent(),
                $query->locale
            )
        );

        if (!$searchFacetViews->hasCategory()) {
            return [];
        }

        $visibleCategories = $this->visibleParticipationCategories->getAllowedCategoriesList($query->sheet);

        $categoryViews = [];

        foreach ($visibleCategories as $category) {
            $categoryViews[] = new CategoryView($category->getId(), $category->getTitle($query->locale));
        }

        return $categoryViews;
    }
}
