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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class CatalogViewQueryHandler
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var NavigationBuilder
     */
    private $navigationBuilder;

    /**
     * HappeningViewQueryHandler constructor.
     *
     * @param DateTimeInterface $dateTime
     * @param NavigationBuilder $navigationBuilder
     */
    public function __construct(DateTimeInterface $dateTime, NavigationBuilder $navigationBuilder)
    {
        $this->dateTime          = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
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

        $linksView = [];

        if ($this->dateTime < $catalogOnlineDate) {
            $linksView[] = new LinkView(
                'navigation.links.catalog.available_date',
                null,
                null,
                new StateButtonView(true, $catalogOnlineDate->format('d m Y'))
            );
        } else {
            $linksView[] = new LinkView(
                'navigation.links.catalog.available_date',
                $this->navigationBuilder->getRoute('event_catalog'),
                null,
                new StateButtonView(true, $catalogOnlineDate->format('d m Y'))
            );
        }

        return new CategoryView(Category::CATALOG, Category::CATALOG_ICON, $linksView);
    }
}
