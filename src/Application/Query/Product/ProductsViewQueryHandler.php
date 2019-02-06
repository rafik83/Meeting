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
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RemoveAuthorizationChecker */
    private $removeAuthorizationChecker;

    /** @var RowRepositoryInterface */
    private $rowRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RowRepositoryInterface     $rowRepository
     * @param RemoveAuthorizationChecker $removeAuthorizationChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RowRepositoryInterface $rowRepository,
        RemoveAuthorizationChecker $removeAuthorizationChecker
    ) {
        $this->productRepository = $productRepository;
        $this->removeAuthorizationChecker = $removeAuthorizationChecker;
        $this->rowRepository = $rowRepository;
    }

    /**
     * @param ProductsViewQuery $query
     *
     * @return ProductView[]
     */
    public function handle(ProductsViewQuery $query): array
    {
        $productViews = [];
        $includedProductViews = [];
        $products = $this->productRepository->findByEventOrderedByProductTypeAndProductname($query->event);
        $this->removeAuthorizationChecker->preloadForEvent($query->event);

        foreach ($products as $product) {
            if (count($product->getIncludedProducts()) > 0) {
                $includedProductViews[] = array_map(function (Product\ProductIncluded $includedProduct) {
                    return [
                        $includedProduct->getIncluded()->getId()  =>  $this->rowRepository->boughtByProduct($includedProduct->getProduct()),
                    ];
                }, $product->getIncludedProducts());
            }

            $bought = $this->rowRepository->boughtByProduct($product);

            $productViews['unit'][] = new ProductView(
                $product->getId(),
                $product->getName(),
                $product->getType(),
                $product->getUnitPrice(),
                $product->isSubjectedToValidation(),
                array_map(function (Product\ProductIncluded $includedProduct) {
                    return [
                        'quantity' => $includedProduct->getQuantity(),
                        'type' => $includedProduct->getIncluded()->getType(),
                        'name' => $includedProduct->getIncluded()->getName(),
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
                $product->getDeletableUntil(),
                $product->hasAvailabilityTimeRanges(),
                $product->isAttributable(),
                $product->hasHappenings()
            );
        }

        $productViews['other'] = $includedProductViews;

        return $productViews;
    }
}
