<?php

namespace Proximum\Vimeet\Application\Components\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Order\DiscountCalculator;

class OrderHelper
{
    /**
     * @param Order $order
     *
     * @return PromotionCode[]
     */
    public static function getModelPromotionCodes(Order $order)
    {
        $identityMap = [];
        foreach ($order->getPromotionCodes() as $orderPromotionCode) {
            $modelPromotionCode = $orderPromotionCode->getPromotionCode();
            $identityMap[$modelPromotionCode->getId()] = $modelPromotionCode;
        }

        return $identityMap;
    }

    /**
     * @param Order         $order
     * @param PromotionCode $modelPromotionCode
     *
     * @return Order\PromotionCode[]
     */
    public static function convertToPromotionCodes(Order $order, PromotionCode $modelPromotionCode): array
    {
        $promotionCodeRows = [];
        foreach ($modelPromotionCode->getPromotions() as $promotion) {
            $discount = self::getDiscountForProduct(
                $order,
                $modelPromotionCode,
                $promotion->getProduct()
            );

            if ($discount < 0) {
                $promotionCodeRows[] = new Order\PromotionCode(
                    $order,
                    $modelPromotionCode,
                    $discount,
                    $promotion->getProduct(),
                    $promotion->getProduct()->getVat()
                );
            }
        }

        return $promotionCodeRows;
    }

    public static function getPromotionCodes(Order $order, PromotionCode $modelPromotionCode)
    {
        $promotionCodeRows = [];
        foreach ($order->getPromotionCodes() as $orderPromotionCode) {
            if ($orderPromotionCode->getPromotionCode() === $modelPromotionCode) {
                $promotionCodeRows[] = $orderPromotionCode;
            }
        }

        return $promotionCodeRows;
    }

    public static function getDiscountForProduct(Order $order, PromotionCode $promotionCode, Product $product): float
    {
        $discountCalculator = new DiscountCalculator();

        // fake order promotion code
        $orderPromotionCode = new Order\PromotionCode($order, $promotionCode, 0.0, $product, 0.0);

        return $discountCalculator->getDiscountForProduct($order, $orderPromotionCode, $product);
    }

    public static function checkPromotionCodes(Order $order)
    {
        foreach ($order->getPromotionCodes() as $promotionCode) {
            if ($promotionCode->getProduct() === null) {
                return false;
            }
        }

        return true;
    }
}
