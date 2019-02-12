<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

interface FixProductPromotionStrategyInterface
{
    /**
     * @param Order $order
     *
     * @return bool
     */
    public function canApply(Order $order): bool;

    /**
     * @param Order $order
     */
    public function fix(Order $order): void;
}
