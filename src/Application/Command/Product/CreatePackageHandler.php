<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreatePackageHandler
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
     * @param CreatePackage $create
     */
    public function handle(CreatePackage $create)
    {
        $product = new Product(
            $create->event,
            Product::TYPE_PACKAGE,
            $create->name,
            $this->fileStorageInterface->upload($create->file),
            $create->unitPrice,
            1,
            $create->availabilityCurrent,
            $create->availabilityMax,
            false,
            null
        );

        foreach ($create->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], $translation['heading'], $translation['description'], $translation['addon']);
        }

        foreach ($create->productIncluded as $productIncluded) {
            $product->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        foreach ($create->features as $feature) {
            $object = new Product\Feature($product);

            foreach ($feature['translations'] as $locale => $translation) {
                $object->translate($locale, $translation['title'], $translation['description']);
            }

            $product->addFeature($object);
        }

        $this->productRepository->add($product);
    }
}
