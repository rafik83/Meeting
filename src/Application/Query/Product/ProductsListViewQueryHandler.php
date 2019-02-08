<?php

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Application\View\Product\ProductsListView;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsListViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RowRepositoryInterface */
    private $rowRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RowRepositoryInterface $rowRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RowRepositoryInterface $rowRepository
    ) {
        $this->productRepository = $productRepository;
        $this->rowRepository = $rowRepository;
    }

    /**
     * @param ProductsListViewQuery $query
     *
     * @return array
     */
    public function handle(ProductsListViewQuery $query): array
    {
        $bought = [];
        $productListViews = [];
        $productIncludedBought = [];
        $products = $this->productRepository->findByEventOrderedByProductTypeAndProductName($query->event);

        foreach ($products as $product) {
            $bought[$product->getId()] = $this->rowRepository->boughtByProduct($product);

            if ('plan' === $product->getType()) {
                if (count($product->getIncludedProducts()) > 0) {
                    $includedProducts = $product->getIncludedProducts();

                    foreach ($includedProducts as $includedProduct) {
                        if (isset($productIncludedBought[$includedProduct->getIncluded()->getId()])) {
                            $productIncludedBought[$includedProduct->getIncluded()->getId()] += $includedProduct->getQuantity() * $bought[$product->getId()];
                        } else {
                            $productIncludedBought[$includedProduct->getIncluded()->getId()] = $includedProduct->getQuantity() * $bought[$product->getId()];
                        }
                    }
                }
            }
        }

        foreach ($products as $product) {
            $unitPrice = $product->getUnitPrice();
            $total = $bought[$product->getId()] + $productIncludedBought[$product->getId()];
            $promotion = 0;

            $productListViews[] = new ProductsListView(
                $product->getName(),
                $unitPrice,
                $bought[$product->getId()],
                $productIncludedBought[$product->getId()],
                $total,
                $promotion,
                ($unitPrice * $total) - $promotion,
            );
        }

        return $productListViews;
    }
}
