<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreatePlanningHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository    = $productRepository;
    }

    /**
     * @param CreatePlanning $createPlanning
     */
    public function handle(CreatePlanning $createPlanning)
    {
        $product = new Product(
            $createPlanning->event,
            Product::TYPE_PLANNING,
            $createPlanning->name,
            null,
            $createPlanning->unitPrice,
            $createPlanning->quantityMax,
            null,
            null,
            true,
            null
        );

        foreach ($createPlanning->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, null, null);
        }

        $this->productRepository->add($product);
    }
}
