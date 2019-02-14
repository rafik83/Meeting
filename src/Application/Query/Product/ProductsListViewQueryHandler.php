<?php

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Application\View\Product\ProductsListView;
use Proximum\Vimeet\Application\View\Product\ProductsView;
use Proximum\Vimeet\Domain\Repository\Order\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsListViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RowRepositoryInterface */
    private $rowRepository;
    
    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;
    
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RowRepositoryInterface $rowRepository,
        PromotionCodeRepositoryInterface $promotionCodeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->rowRepository = $rowRepository;
        $this->promotionCodeRepository = $promotionCodeRepository;
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
        $promotions = $this->promotionCodeRepository->findPrices();
        dump($promotions);die;

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
            $unitPrice = $product->getUnitPrice();
            $boughtInt = $bought[$product->getId()];
            $productIncludedBoughtInt = 0;
            
            if (array_key_exists($product->getId(), $productIncludedBought)) {
                $productIncludedBoughtInt = $productIncludedBought[$product->getId()];
            }
            
            if ($boughtInt === 0 && $productIncludedBoughtInt === 0) {
                continue;
            }
            
            $total = $boughtInt + $productIncludedBoughtInt;
            $promotion = 0;
            
            $productViews[] = new ProductsView(
                $product->getName(),
                $unitPrice,
                $boughtInt,
                $productIncludedBoughtInt,
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
