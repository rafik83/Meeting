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

class CreateOptionHandler
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
     * @param CreateOption $create
     */
    public function handle(CreateOption $create)
    {
        $product = new Product(
            $create->event,
            Product::TYPE_OPTION,
            $create->name,
            $this->fileStorageInterface->upload($create->file),
            $create->unitPrice,
            $create->quantityMax,
            $create->availabilityCurrent,
            $create->availabilityMax,
            $create->updatable,
            $create->updatableUntil
        );

        foreach ($create->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], $translation['addon']);
        }

        $this->productRepository->add($product);
    }
}
