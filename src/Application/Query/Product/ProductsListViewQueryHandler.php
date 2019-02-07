<?php

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductsListViewQueryHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductsListViewQuery $query
     *
     * @return array
     */
    public function handle(ProductsListViewQuery $query): array
    {
        $productListViews = [];
        $products = $this->productRepository->findByEventOrderedByProductTypeAndProductname($query->event);
    }
}
