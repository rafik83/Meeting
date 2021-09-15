<?php

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
        $bought = [];
        $productIncludedBought = [];
        $products = $this->productRepository->findByEventOrderedByProductTypeAndProductName($query->event);
        $this->removeAuthorizationChecker->preloadForEvent($query->event);

        foreach ($products as $product) {
            $bought[$product->getId()] = $this->rowRepository->boughtByProduct($product);

            if ($product->isPlan() && $product->hasIncludedProducts()) {
                $includedProducts = $product->getIncludedProducts();

                foreach ($includedProducts as $includedProduct) {
                    $includedProductId = $includedProduct->getIncluded()->getId();

                    if (isset($productIncludedBought[$includedProductId])) {
                        $productIncludedBought[$includedProductId] += $includedProduct->getQuantity() * $bought[$product->getId()];
                    } else {
                        $productIncludedBought[$includedProductId] = $includedProduct->getQuantity() * $bought[$product->getId()];
                    }
                }
            }
        }

        foreach ($products as $product) {
            $productViews[] = new ProductView(
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
                $bought[$product->getId()],
                $productIncludedBought[$product->getId()] ?? 0,
                $this->removeAuthorizationChecker->canBeRemoved($product),
                $product->getAvailabilityStatus($bought[$product->getId()]),
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

        return $productViews;
    }
}
