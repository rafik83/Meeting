<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;

interface ParticipantWithAttributedProductRepositoryInterface
{
    /**
     * @param Participant[] $participants
     * @param Product       $product
     *
     * @return Participant[]
     */
    public function getParticipantsWithAttributedProduct(array $participants, Product $product): array;
}
