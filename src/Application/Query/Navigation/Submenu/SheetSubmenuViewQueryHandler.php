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
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class SheetSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

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
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param CatalogAccessChecker       $catalogAccessChecker
     * @param HappeningsAccessChecker    $happeningsAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        CartRowRepositoryInterface $cartRowRepository,
        CatalogAccessChecker       $catalogAccessChecker,
        HappeningsAccessChecker    $happeningsAccessChecker
    ) {
        $this->navigationBuilder       = $navigationBuilder;
        $this->cartRowRepository       = $cartRowRepository;
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
        if ($this->happeningsAccessChecker->allowedToAccess($query->event)
            && (!$query->sheet->getPackage()->isPassable() || $query->sheet->hasOrders())
        ) {
            $buttonViews[] = new SubmenuButtonView(
                Category::PLANNING_ICON,
                'program.title',
                $this->navigationBuilder->getRoute('happening_program'),
                false
            );
        }

        $package = $query->sheet->getPackage();

        // Package button
        if ((!$query->sheet->hasNotCancelledOrders() || $this->cartRowRepository->hasProducts($query->sheet))
            && $package !== null
            && $package->isPassable() === true
        ) {
            $hasProductsInCartRow = $this->cartRowRepository->hasProducts($query->sheet);

            $buttonViews[] = new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'package.title',
                $this->navigationBuilder->getRoute('event_package'),
                Route::isPackage($query->route),
                $hasProductsInCartRow === true
            );
        }

        return $buttonViews;
    }
}
