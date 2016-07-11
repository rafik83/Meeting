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

class SheetViewQueryHandler
{
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
                '',
                $locale
            );
        }

        return new CategoryView(
            Category::SHEET,
            Category::SHEET_ICON,
            $linksView
        );
    }
}
