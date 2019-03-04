<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

interface FixProductPromotionStrategyInterface
{
    public function canApply(Order $order): bool;

    public function fix(Order $order): void;
}
