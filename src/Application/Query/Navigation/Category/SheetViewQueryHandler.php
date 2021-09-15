<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Adapter\LocaleHelperInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class SheetViewQueryHandler
{
    const EVENT_SHEET_ROUTE = 'event_sheet_locale';

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var LocaleHelperInterface */
    private $localeHelper;

    /**
     * SheetViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param LocaleHelperInterface      $localeHelper
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder, LocaleHelperInterface $localeHelper)
    {
        $this->navigationBuilder = $navigationBuilder;
        $this->localeHelper = $localeHelper;
    }

    /**
     * @param SheetViewQuery $sheetQuery
     *
     * @return CategoryView
     */
    public function handle(SheetViewQuery $sheetQuery)
    {
        $linksView = [];

        foreach ($sheetQuery->sheet->getEvent()->getLocales() as $locale) {
            $linksView[] = new LinkView(
                ucfirst($this->localeHelper->language($locale, $sheetQuery->locale)),
                $this->navigationBuilder->getRoute(
                    self::EVENT_SHEET_ROUTE,
                    ['sheet' => $sheetQuery->sheet->getId(), 'locale' => $locale]
                ),
                $locale
            );
        }

        $categoryTitle = Category::SHEET;

        if (null !== $sheetQuery->staticFormulation) {
            $categoryTitle = $sheetQuery->staticFormulation->getTitle($sheetQuery->locale);
        }

        return new CategoryView($categoryTitle, Category::SHEET_ICON, $linksView, true);
    }
}
