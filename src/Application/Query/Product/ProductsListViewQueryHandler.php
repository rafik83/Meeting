<?php

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Application\View\Product\ProductsListView;
use Proximum\Vimeet\Application\View\Product\ProductsView;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsListViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RowRepositoryInterface */
    private $rowRepository;

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
     * @return ProductsListView
     */
    public function handle(ProductsListViewQuery $query): ProductsListView
    {
        $bought = [];
        $productViews = [];
        $productIncludedBought = [];
        $products = $this->productRepository->findByEventOrderedByProductTypeAndProductName($query->event);

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
            $unitPrice = (int) $product->getUnitPrice();
            $bought = $bought[$product->getId()] ?? 0;
            $productIncludedBought = $productIncludedBought[$product->getId()] ?? 0;
    
            if ($bought === 0 && $productIncludedBought === 0) {
                continue;
            }
            
            $total = $bought + $productIncludedBought;
            $promotion = 0;

            $productViews[] = new ProductsView(
                $product->getName(),
                $unitPrice,
                $bought,
                $productIncludedBought,
                $total,
                $promotion,
                ($unitPrice * $total) - $promotion
            );
        }

        return new ProductsListView(
            $productViews,
            $query->adminLocale
        );
    }
}
