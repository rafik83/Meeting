<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

class ProgramViewQueryHandler
{
    /** @var HappeningsAccessChecker */
    private $happeningsAccessChecker;

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->happeningsAccessChecker = $happeningsAccessChecker;
        $this->navigationBuilder = $navigationBuilder;
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(ProgramViewQuery $programViewQuery): ?CategoryView
    {
        if (!$this->happeningsAccessChecker->allowedToAccess($programViewQuery->sheet->getEvent())) {
            return null;
        }

        $linksView = [];

        $categoryListViews = $this->categoryRepository->getCategoryListViewByType(
            $programViewQuery->sheet->getType(),
            $programViewQuery->locale
        );

        if (empty($categoryListViews)) {
            return null;
        }

        foreach ($categoryListViews as $categoryListView) {
            $linksView[] = new LinkView(
                $categoryListView->title,
                $this->navigationBuilder->getRoute('happening_program', ['sheet' => $programViewQuery->sheet->getId()]),
                null
            );
        }

        $categoryTitle = Category::PROGRAM;

        if (null !== $programViewQuery->staticFormulation) {
            $categoryTitle = $programViewQuery->staticFormulation->getTitle($programViewQuery->locale);
        }

        return new CategoryView(
            $categoryTitle,
            Category::PROGRAM_ICON,
            $linksView,
            true
        );
    }
}
