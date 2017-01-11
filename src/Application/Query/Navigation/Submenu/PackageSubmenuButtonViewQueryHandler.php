<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class PackageSubmenuButtonViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /**
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
     * @param PackageSubmenuButtonViewQuery $query
     *
     * @return null|SubmenuButtonView
     */
    public function handle(PackageSubmenuButtonViewQuery $query)
    {
        $package           = $query->sheet->getPackage();
        $hasProductsInCart = $this->cartRowRepository->hasProducts($query->sheet);

        // Package button
        if ((!$query->sheet->hasNotCancelledOrders() || $hasProductsInCart)
            && $package !== null
            && $package->isPassable() === true
        ) {
            return new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'package.title',
                $this->navigationBuilder->getRoute('event_package'),
                Route::isPackage($query->route),
                $hasProductsInCart === true
            );
        }

        return null;
    }
}
