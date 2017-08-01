<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class Duplicator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * Duplicator constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param FileStorageInterface       $fileStorage
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->productRepository = $productRepository;
        $this->fileStorage       = $fileStorage;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $products = $this->productRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($products as $product) {
            $productToAdd = Product::createProductFromType(
                $product->getType(),
                $event,
                $product->getName(),
                $this->fileStorage->copyAndRename($product->getImage()),
                $product->getUnitPrice(),
                $product->getQuantityMax(),
                $product->getAvailabilityCurrent(),
                $product->getAvailabilityMax(),
                $product->isUpdatable(),
                $product->getDeletableUntil(),
                $product->isSubjectedToValidation(),
                $product->getBuyableUntil()
            );

            if ($product->getType() === Product::TYPE_PLAN) {
                $this->handlePlanProductsAndFeatures($productToAdd, $product);
            }

            foreach ($product->getTranslationsData() as $locale => $translation) {
                if (in_array($locale, $event->getLocales())) {
                    $title       = $translation['title'] ?? '';
                    $description = $translation['description'] ?? '';
                    $addon       = $translation['addon'] ?? '';

                    $subjectedToValidationHelp = $translation['subjectedToValidationHelp'] ?? '';

                    $product->translate(
                        $locale,
                        $title,
                        null,
                        $description,
                        $addon,
                        $subjectedToValidationHelp
                    );
                }
            }
            $this->productRepository->add($productToAdd);
        }
    }

    /**
     * @param Product $productToAdd
     * @param Product $product
     */
    public function handlePlanProductsAndFeatures(Product $productToAdd, Product $product)
    {
        foreach ($product->getIncludedProducts() as $productIncluded) {
            $productToAdd->includeProduct($productIncluded->getIncluded(), $productIncluded->getQuantity());
        }

        foreach ($product->getFeatures() as $feature) {
            $object = new Product\Feature($productToAdd);

            foreach ($feature->getTranslations()->toArray() as $locale => $translation) {
                $object->translate($locale, $translation->getTitle(), $translation->getDescription());
            }

            $productToAdd->addFeature($object);
        }
    }
}
