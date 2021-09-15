<?php

namespace Proximum\Vimeet\Domain\Repository\Order;

use Proximum\Vimeet\Domain\Model\Order\PromotionCode;

interface PromotionCodeRepositoryInterface
{
    public function remove(PromotionCode $promotionCode): void;

    public function findPrices(): array;
}
