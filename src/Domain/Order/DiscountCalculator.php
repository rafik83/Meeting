<?php

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;
use Proximum\Vimeet\Domain\Model\Product;

class DiscountCalculator
{
    /**
     * @param Order         $order
     * @param PromotionCode $promotionCode
     * @param Product       $product
     *
     * @return float
     */
    public function getDiscountForProduct(Order $order, PromotionCode $promotionCode, Product $product): float
    {
        $row = $order->getRowForProduct($product);

        if (null === $row) {
            return 0;
        }

        $total = 0;

        foreach ($promotionCode->getPromotionCode()->getPromotions() as $promotion) {
            $total += $promotion->getDiscountAmountForProduct($product, $row->getQuantity());
        }

        return $total;
    }
}
