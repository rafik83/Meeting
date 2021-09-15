<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class PackageSubmenuButtonViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var CartManager */
    private $cartManager;

    /**
     * @param NavigationBuilderInterface $navigationBuilder
     * @param CartManager                $cartManager
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        CartManager $cartManager
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->cartManager = $cartManager;
    }

    /**
     * @param PackageSubmenuButtonViewQuery $query
     *
     * @return null|SubmenuButtonView
     */
    public function handle(PackageSubmenuButtonViewQuery $query)
    {
        $package = $query->sheet->getPackage();

        // Package button
        if (null !== $package && true === $package->isPassable()) {
            $route = 'event_package_redirect_depending_on_context';

            $cart = $this->cartManager->getCart($query->sheet);
            $hasProductsInCart = $cart->hasProducts();

            if ($query->sheet->hasNotCancelledOrders() && !$hasProductsInCart) {
                $route = 'event_order_summary_total';
            }

            $subMenuTitle = 'navigation.category.package';

            $absoluteProductsQuantity = $cart->getAbsoluteProductsQuantity();

            if ($absoluteProductsQuantity > 0) {
                $subMenuTitle = 'navigation.category.inCart';
            } else if (null !== $query->staticFormulation) {
                $subMenuTitle = $query->staticFormulation->getTitle($query->locale);
            }

            return new SubmenuButtonView(
                Category::PACKAGE_ICON,
                $subMenuTitle,
                $this->navigationBuilder->getRoute($route, ['sheet' => $query->sheet->getId()]),
                Route::isPackage($query->route),
                $absoluteProductsQuantity ?: null
            );
        }

        return null;
    }
}
