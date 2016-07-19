<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;

class HappeningViewQueryHandler
{
    /**
     * @param HappeningViewQuery $happeningViewQuery
     *
     * @return CategoryView
     */
    public function handle(HappeningViewQuery $happeningViewQuery)
    {
        $happeningOpenDate = $happeningViewQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getHappeningsOpenDate();

        $linksView = [];

        if ($happeningOpenDate === null) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } else {
            $formatter = new IntlDateFormatter(
                $happeningViewQuery->locale,
                IntlDateFormatter::LONG,
                IntlDateFormatter::LONG
            );
            $formatter->setPattern('d MMMM Y');
            $happeningOpenDateFormatted = $formatter->format($happeningOpenDate);

            $linksView[] = new LinkView(
                'navigation.links.happening.open_date',
                null,
                null,
                new StateButtonView(false, $happeningOpenDateFormatted ? $happeningOpenDateFormatted : '')
            );
        }

        return new CategoryView(Category::HAPPENING, Category::HAPPENING_ICON, $linksView);
    }
}
