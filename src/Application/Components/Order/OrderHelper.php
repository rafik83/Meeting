<?php

namespace Proximum\Vimeet\Application\Components\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Order\DiscountCalculator;

class OrderHelper
{
    /** @var DiscountCalculator */
    private  $discountCalculator;
    
    public function __construct(DiscountCalculator $discountCalculator)
    {
       $this->discountCalculator = $discountCalculator;
    }
    
    /**
     * @param Order $order
     *
     * @return PromotionCode[]
     */
    public function getModelPromotionCodes(Order $order): array
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
    public function convertToPromotionCodes(Order $order, PromotionCode $modelPromotionCode): array
    {
        $promotionCodeRows = [];
        
        foreach ($modelPromotionCode->getPromotions() as $promotion) {
            $discount = $this->getDiscountForProduct(
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

    public function getPromotionCodes(Order $order, PromotionCode $modelPromotionCode): array
    {
        $promotionCodeRows = [];
        
        foreach ($order->getPromotionCodes() as $orderPromotionCode) {
            if ($orderPromotionCode->getPromotionCode() === $modelPromotionCode) {
                $promotionCodeRows[] = $orderPromotionCode;
            }
        }

        return $promotionCodeRows;
    }

    public function getDiscountForProduct(Order $order, PromotionCode $promotionCode, Product $product): float
    {
        // fake order promotion code
        $orderPromotionCode = new Order\PromotionCode($order, $promotionCode, 0.0, $product, 0.0);
        
        return $this->discountCalculator->getDiscountForProduct($order, $orderPromotionCode, $product);
    }

    public function checkPromotionCodes(Order $order): bool
    {
        foreach ($order->getPromotionCodes() as $promotionCode) {
            if (null === $promotionCode->getProduct()) {
                return false;
            }
        }

        return true;
    }
}
