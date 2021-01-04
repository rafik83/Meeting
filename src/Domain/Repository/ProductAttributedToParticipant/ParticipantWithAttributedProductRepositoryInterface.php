<?php

namespace Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;

interface ParticipantWithAttributedProductRepositoryInterface
{
    /**
     * @param Participant[] $participants
     *
     * @return Participant[]
     */
    public function getParticipantsWithAttributedProduct(array $participants): array;

    /**
     * @param Participant[] $participants
     * @param Product       $product
     *
     * @return Participant[]
     */
    public function getParticipantsWithAttributedProductForProduct(array $participants, Product $product): array;
}
