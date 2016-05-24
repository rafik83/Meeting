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
use Proximum\Vimeet\Domain\Model\ProductTranslation;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class CreateHandler
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $product = new Product(
            $create->event,
            $create->name,
            $this->fileStorageInterface->upload($create->file),
            $create->unitPrice,
            $create->quantityMin,
            $create->quantityMax,
            $create->availabilityCurrent,
            $create->availabilityMax,
            $create->updatable,
            $create->updatableUntil
        );

        foreach ($create->translations as $locale => $translation) {
            $product->getTranslations()->set(
                $locale,
                new ProductTranslation(
                    $product,
                    $locale,
                    $translation['title'],
                    $translation['description'],
                    $translation['optionalPriceText']
                )
            );
        }

        $this->productRepository->add($product);
    }
}
