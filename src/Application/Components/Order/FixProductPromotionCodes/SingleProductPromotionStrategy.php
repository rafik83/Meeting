<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

/**
 * Class SingleProductPromotionStrategy
 *
 * (3rd strategy) When for every promotion there's only one matching product
 *
 * It doesn't check discounts since there's only one possible product per promotion code.
 *
 * @package Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes
 */
class SingleProductPromotionStrategy implements FixProductPromotionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canApply(Order $order): bool
    {
        if (count($order->getPromotionCodes()) <= 0) {
            return false;
        }

        foreach ($order->getPromotionCodes() as $orderPromotionCode) {

            $promotionCode = $orderPromotionCode->getPromotionCode();

            $matchingProduct = 0;
            foreach ($order->getRows() as $row) {
                if ($row->getProduct() === null) {
                    continue;
                }
                $promotion = $promotionCode->getPromotion($row->getProduct());
                if ($promotion) {
                    $matchingProduct++;
                }
            }

            if ($matchingProduct !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(Order $order): void
    {
        if (false === $this->canApply($order)) {
            throw new \BadMethodCallException('Can\'t apply this strategy to this order');
        }

        $matchingProduct = null;

        foreach ($order->getPromotionCodes() as $orderPromotionCode) {
            $promotionCode = $orderPromotionCode->getPromotionCode();

            foreach ($order->getRows() as $row) {
                if (null === $row->getProduct()) {
                    continue;
                }
                $promotion = $promotionCode->getPromotion($row->getProduct());
                if ($promotion) {
                    $matchingProduct = $row->getProduct();
                }
            }

            $orderPromotionCode->setProduct($matchingProduct);
        }
    }
}
