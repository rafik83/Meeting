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
     * SheetSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        CartRowRepositoryInterface $cartRowRepository
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->cartRowRepository = $cartRowRepository;
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
            !Route::isPackage($query->route)
        );

        if ($query->sheet->getPackage() !== null && $query->sheet->getPackage()->isPassable() === true) {
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
