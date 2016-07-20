<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class SheetViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * SheetViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder)
    {
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param SheetViewQuery $sheetQuery
     *
     * @return CategoryView
     */
    public function handle(SheetViewQuery $sheetQuery)
    {
        $linksView = [];

        foreach($sheetQuery->sheet->getEvent()->getLocales() as $locale) {
            $linksView[] = new LinkView(
                'navigation.links.sheet.locale',
                $this->navigationBuilder->getRoute('event_sheet_locale', ['locale' => $locale]),
                $locale
            );
        }

        return new CategoryView(Category::SHEET, Category::SHEET_ICON, $linksView);
    }
}
