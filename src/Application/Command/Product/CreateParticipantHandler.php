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

class CreateParticipantHandler
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
