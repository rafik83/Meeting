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
     * SheetSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param CatalogAccessChecker       $catalogAccessChecker
     * @param HappeningsAccessChecker    $happeningsAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        CatalogAccessChecker $catalogAccessChecker,
        HappeningsAccessChecker $happeningsAccessChecker
    ) {
        $this->navigationBuilder       = $navigationBuilder;
        $this->catalogAccessChecker    = $catalogAccessChecker;
        $this->happeningsAccessChecker = $happeningsAccessChecker;
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

        return $buttonViews;
    }
}
