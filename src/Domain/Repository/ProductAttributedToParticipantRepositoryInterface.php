<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;

interface ProductAttributedToParticipantRepositoryInterface
{
    public function add(ProductAttributedToParticipant $productAttributedToParticipant): void;

    /**
     * @param Product       $product
     * @param Participant[] $participants
     *
     * @return ProductAttributedToParticipant[]
     */
    public function findByProductAndParticipants(Product $product, array $participants): array;

    /**
     * @param ProductAttributedToParticipant[] $productAttributedToParticipants
     */
    public function remove(array $productAttributedToParticipants): void;
}
