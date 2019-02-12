<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

/**
 * Class SinglePromoOneMatchingProductStrategy
 *
 * (2nd strategy) When there's only one promotion code and it matches with only one product
 *
 * It doesn't check discounts since there's only one possible product.
 *
 * @package Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes
 */
class SinglePromoOneMatchingProductStrategy implements FixProductPromotionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canApply(Order $order): bool
    {
        if (count($order->getPromotionCodes()) !== 1) {
            return false;
        }

        $matchingProduct = 0;

        $promotionCode = $order->getPromotionCodes()[0]->getPromotionCode();

        foreach ($order->getRows() as $row) {
            if ($row->getProduct() === null) {
                continue;
            }
            $promotion = $promotionCode->getPromotion($row->getProduct());
            if ($promotion) {
                $matchingProduct++;
            }
        }

        return $matchingProduct === 1;
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

        $orderPromotionCode = $order->getPromotionCodes()[0];
        $promotionCode = $orderPromotionCode->getPromotionCode();

        foreach ($order->getRows() as $row) {
            if ($row->getProduct() === null) {
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
