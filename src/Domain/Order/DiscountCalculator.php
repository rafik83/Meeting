<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;

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
            if ($promotion->getProduct() !== $product) {
                continue;
            }

            // don't apply promo code on order row negative quantity
            if ($row->getQuantity() < 0) {
                continue;
            }

            // don't use promotion quantity max if promotion type value off
            if (Promotion::TYPE_VALUE_OFF === $promotion->getType()) {
                $total -= $promotion->getDiscount();
            } elseif ($row->getQuantity() < $promotion->getQuantityMax()
                || null === $promotion->getQuantityMax()
            ) {
                $total -= $row->getQuantity() * $promotion->getDiscount();
            } else {
                $total -= $promotion->getQuantityMax() * $promotion->getDiscount();
            }
        }

        return $total;
    }
}
