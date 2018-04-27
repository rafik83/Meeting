<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
        if (null !== $package && true === $package->isPassable()) {
            $route = 'event_package_redirect_depending_on_context';

            if ($query->sheet->hasNotCancelledOrders() && !$hasProductsInCart) {
                $route = 'event_order_summary_total';
            }

            return new SubmenuButtonView(
                Category::PACKAGE_ICON,
                'navigation.category.package',
                $this->navigationBuilder->getRoute($route, ['sheet' => $query->sheet->getId()]),
                Route::isPackage($query->route),
                true === $hasProductsInCart
            );
        }

        return null;
    }
}
