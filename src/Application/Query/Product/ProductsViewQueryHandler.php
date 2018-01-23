<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Application\View\Product\ProductView;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RemoveAuthorizationChecker */
    private $removeAuthorizationChecker;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RemoveAuthorizationChecker $removeAuthorizationChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RemoveAuthorizationChecker $removeAuthorizationChecker
    ) {
        $this->productRepository = $productRepository;
        $this->removeAuthorizationChecker = $removeAuthorizationChecker;
    }

    /**
     * @param ProductsViewQuery $query
     *
     * @return ProductView[]
     */
    public function handle(ProductsViewQuery $query): array
    {
        $productViews = [];
        $products = $this->productRepository->countByEvent($query->event);
        $this->removeAuthorizationChecker->preloadForEvent($query->event);

        foreach ($products as $boughtAndProduct) {
            /** @var Product $product */
            $product = $boughtAndProduct[0];
            $bought = (int) $boughtAndProduct['bought'];

            $productViews[] = new ProductView(
                $product->getId(),
                $product->getName(),
                $product->getType(),
                $product->getUnitPrice(),
                $product->isSubjectedToValidation(),
                array_map(function (Product\ProductIncluded $includedProduct) {
                    return [
                        'quantity' => $includedProduct->getQuantity(),
                        'name'     => $includedProduct->getIncluded()->getName()
                    ];
                }, $product->getIncludedProducts()),
                $bought,
                $this->removeAuthorizationChecker->canBeRemoved($product),
                $product->getAvailabilityStatus($bought),
                $product->isAvailabilityManaged(),
                $product->isUpdatable(),
                $product->getQuantityMax(),
                $product->getAvailabilityCurrent(),
                $product->getAvailabilityMax(),
                $product->getBuyableUntil(),
                $product->getDeletableUntil()
            );
        }

        return $productViews;
    }
}
