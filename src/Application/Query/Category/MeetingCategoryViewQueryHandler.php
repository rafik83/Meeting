<?php

namespace Proximum\Vimeet\Application\Query\Category;

use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\CategoryView;

class MeetingCategoryViewQueryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var SearchFacetViewQueryHandler */
    private $searchFacetViewQueryHandler;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchFacetViewQueryHandler    $searchFacetViewQueryHandler
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SearchFacetViewQueryHandler $searchFacetViewQueryHandler
    ) {
        $this->categoryRepository = $categoryRepository;
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

        $visibleCategories = $this->categoryRepository->getFromSheetMeetingRequests($query->sheet, $query->locale);

        $categoryViews = [];

        foreach ($visibleCategories as $category) {
            $categoryViews[] = new CategoryView($category->getId(), $category->getTitle($query->locale));
        }

        return $categoryViews;
    }
}
