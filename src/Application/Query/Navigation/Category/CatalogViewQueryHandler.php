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
use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class CatalogViewQueryHandler
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * CatalogViewQueryHandler constructor.
     *
     * @param DateTimeInterface   $dateTime
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(DateTimeInterface $dateTime, NavigationBuilderInterface $navigationBuilder)
    {
        $this->dateTime          = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param CatalogViewQuery $catalogViewQuery
     *
     * @return CategoryView
     */
    public function handle(CatalogViewQuery $catalogViewQuery)
    {
        $catalogOnlineDate = $catalogViewQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getCatalogOnlineDate();

        $linksView = [];

        if (empty($catalogOnlineDate)) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } else {
            $formatter = new IntlDateFormatter($catalogViewQuery->locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG);
            $formatter->setPattern('d MMMM Y');

            if ($this->dateTime < $catalogOnlineDate) {
                $linksView[] = new LinkView(
                    'navigation.links.catalog.available_date',
                    null,
                    null,
                    new StateButtonView(true, $formatter->format($catalogOnlineDate))
                );
            } else {
                $linksView[] = new LinkView(
                    'navigation.links.catalog.available_date',
                    $this->navigationBuilder->getRoute('event_catalog'),
                    null,
                    new StateButtonView(true, $formatter->format($catalogOnlineDate))
                );
            }
        }

        return new CategoryView(Category::CATALOG, Category::CATALOG_ICON, $linksView);
    }
}
