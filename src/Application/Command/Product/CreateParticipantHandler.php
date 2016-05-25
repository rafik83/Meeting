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

class CreateParticipantHandler
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
     * @param CreateParticipant $createParticipant
     */
    public function handle(CreateParticipant $createParticipant)
    {
        $product = new Product(
            $createParticipant->event,
            Product::TYPE_PARTICIPANT,
            $createParticipant->name,
            null,
            $createParticipant->unitPrice,
            $createParticipant->quantityMax,
            null,
            null,
            true,
            null
        );

        foreach ($createParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, null, null);
        }

        $this->productRepository->add($product);
    }
}
