<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class CatalogSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param DateTimeInterface          $datetime
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder, DateTimeInterface $datetime)
    {
        $this->navigationBuilder = $navigationBuilder;
        $this->datetime          = $datetime;
    }

    /**
     * @param CatalogSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(CatalogSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $catalogOnlineDate = $query->event->getConfiguration()->getCatalogOnlineDate();

        if (null !== $catalogOnlineDate && $catalogOnlineDate <= $this->datetime && $query->sheet->isInCatalog()) {
            $buttonViews[] = new SubmenuButtonView(
                Category::CATALOG_ICON,
                'navigation.category.catalog',
                $this->navigationBuilder->getRoute('event_catalog_index', ['sheet' => $query->sheet->getId()]),
                Route::isCatalog($query->route),
                false,
                true
            );

            $buttonViews[] = new SubmenuButtonView(
                Category::MEETING_ICON,
                'navigation.category.meeting',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $query->sheet->getId(),
                ]),
                Route::isMeetingRequest($query->route),
                false,
                true
            );
        }

        return $buttonViews;
    }
}
