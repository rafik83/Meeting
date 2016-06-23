<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Plan;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreatePlanHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorageInterface;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FileStorageInterface       $fileStorageInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FileStorageInterface $fileStorageInterface
    ) {
        $this->productRepository    = $productRepository;
        $this->fileStorageInterface = $fileStorageInterface;
    }

    /**
     * @param CreatePlan $createPlan
     */
    public function handle(CreatePlan $createPlan)
    {
        $product = Product::createPlan(
            $createPlan->event,
            $createPlan->name,
            $this->fileStorageInterface->upload($createPlan->file),
            $createPlan->unitPrice,
            $createPlan->availabilityCurrent,
            $createPlan->availabilityMax
        );

        foreach ($createPlan->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], $translation['heading'], $translation['description'], $translation['addon'], null);
        }

        foreach ($createPlan->productIncluded as $productIncluded) {
            $product->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        foreach ($createPlan->features as $feature) {
            $object = new Product\Feature($product);

            foreach ($feature['translations'] as $locale => $translation) {
                $object->translate($locale, $translation['title'], $translation['description']);
            }

            $product->addFeature($object);
        }

        $this->productRepository->add($product);
    }
}
