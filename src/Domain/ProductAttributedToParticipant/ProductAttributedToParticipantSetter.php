<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductAttributedToParticipantSetter
{
    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->dateTime = $dateTime;
    }

    public function attributeProductToParticipant(Product $product, Participant $participant): void
    {
        $this->productAttributedToParticipantRepository->add(
            new ProductAttributedToParticipant($product, $participant, $this->dateTime)
        );
    }
}
