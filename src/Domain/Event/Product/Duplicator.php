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
        $results = $this->productRepository->countByEvent($event->getDuplicatedFrom());

        foreach ($results as $result) {

            /** @var Product $product */
            $product = $result[0];

            $productToAdd = Product::createOption(
                $event,
                $product->getName(),
                $this->fileStorage->copyAndRename($product->getImage()),
                $product->getUnitPrice(),
                $product->getQuantityMax(),
                $product->getAvailabilityCurrent(),
                $product->getAvailabilityMax(),
                $product->isUpdatable(),
                $product->getDeletableUntil(),
                $product->isSubjectedToValidation()
            );

            foreach ($product->getTranslationsData() as $locale => $translation) {
                if (in_array($locale, $event->getLocales())) {
                    $title       = isset($translation['title']) ? $translation['title'] : '';
                    $description = isset($translation['description']) ? $translation['description'] : '';
                    $addon       = isset($translation['addon']) ? $translation['addon'] : '';

                    $subjectedToValidationHelp = isset($translation['subjectedToValidationHelp']) ?
                        $translation['subjectedToValidationHelp']
                        : '';

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
}
