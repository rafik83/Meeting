<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class SheetSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var CatalogAccessChecker
     */
    private $catalogAccessChecker;

    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * @var PackageSubmenuButtonViewQueryHandler
     */
    private $packageSubmenuButtonViewQueryHandler;

    /**
     * SheetSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface           $navigationBuilder
     * @param PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler
     * @param CatalogAccessChecker                 $catalogAccessChecker
     * @param HappeningsAccessChecker              $happeningsAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler,
        CatalogAccessChecker $catalogAccessChecker,
        HappeningsAccessChecker $happeningsAccessChecker
    ) {
        $this->navigationBuilder                    = $navigationBuilder;
        $this->packageSubmenuButtonViewQueryHandler = $packageSubmenuButtonViewQueryHandler;
        $this->catalogAccessChecker                 = $catalogAccessChecker;
        $this->happeningsAccessChecker              = $happeningsAccessChecker;
    }

    /**
     * @param SheetSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(SheetSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $buttonViews[] = new SubmenuButtonView(
            Category::SHEET_ICON,
            'sheet.title',
            $this->navigationBuilder->getRoute('event_sheet'),
            Route::isSheet($query->route)
        );

        // Catalog button
        if ($query->sheet->isInCatalog() && $this->catalogAccessChecker->allowedToAccess($query->event)) {
            $buttonViews[] = new SubmenuButtonView(
                Category::CATALOG_ICON,
                'catalog.title',
                $this->navigationBuilder->getRoute('event_catalog_index'),
                false
            );
        }

        // Program button
        if ($this->happeningsAccessChecker->allowedToAccess($query->event)) {
            $buttonViews[] = new SubmenuButtonView(
                Category::PLANNING_ICON,
                'program.title',
                $this->navigationBuilder->getRoute('happening_program'),
                false
            );
        }

        // Package button
        $packageSubmenuButtonView = $this->packageSubmenuButtonViewQueryHandler->handle(
            new PackageSubmenuButtonViewQuery($query->sheet, $query->route)
        );

        if (null !== $packageSubmenuButtonView) {
            $buttonViews[] = $packageSubmenuButtonView;
        }

        return $buttonViews;
    }
}
