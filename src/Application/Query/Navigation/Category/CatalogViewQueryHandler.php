<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;

class CatalogViewQueryHandler
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * HappeningViewQueryHandler constructor.
     *
     * @param DateTimeInterface $dateTime
     */
    public function __construct(DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     *
     * @param CatalogViewQuery $catalogViewQuery
     *
     * @return CategoryView
     */
    public function handle(CatalogViewQuery $catalogViewQuery)
    {
        $catalogOnlineDate = $catalogViewQuery->sheet->getEvent()
                                                     ->getConfiguration()
                                                     ->getCatalogOnlineDate();

        if (empty($catalogOnlineDate)) {
            return null;
        }

        $linksView   = [];
        $linksView[] = new LinkView(
            'navigation.links.catalog.available_date',
            '',
            $catalogViewQuery->user->getLocale(),
            new StateButtonView(true, $catalogOnlineDate->format('c'))
        );

        return new CategoryView(
            Category::CATALOG,
            Category::CATALOG_ICON,
            $linksView
        );
    }
}
