<?php

namespace Proximum\Vimeet\Domain\Repository\Order;

interface PromotionCodeRepositoryInterface
{
    public function findPrices(): array;
}
