<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;

class CategoryViewQueryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param CategoryViewQuery $query
     *
     * @return CategoryView[]
     */
    public function handle(CategoryViewQuery $query): array
    {
        $categories = $this->categoryRepository->getCategoriesTitleByEventAndLocale(
            $query->event,
            $query->locale,
            $query->visibleCategories
        );

        $categoryViews = [];

        foreach ($categories as $id => $title) {
            $categoryViews[$id] = new CategoryView($id, $title, 0);
        }

        return $categoryViews;
    }
}
