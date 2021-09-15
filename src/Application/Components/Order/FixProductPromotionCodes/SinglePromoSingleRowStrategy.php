<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

/**
 * Class SinglePromoSingleRowStrategy
 *
 * (1st strategy) When there's only one product and only one promotion code
 *
 * It doesn't check discounts since there's only one possible product.
 *
 * @package Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes
 */
class SinglePromoSingleRowStrategy implements FixProductPromotionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canApply(Order $order): bool
    {
        if (count($order->getPromotionCodes()) !== 1) {
            return false;
        }

        $productCount = 0;
        foreach ($order->getRows() as $row) {
            if ($row->getProduct()) {
                $productCount++;
            }
        }

        return $productCount === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(Order $order): void
    {
        if (false === $this->canApply($order)) {
            throw new \BadMethodCallException('Can\'t apply this strategy to this order');
        }

        $orderPromotionCode = $order->getPromotionCodes()[0];

        $product = null;
        foreach ($order->getRows() as $row) {
            if ($row->getProduct()) {
                $product = $row->getProduct();
            }
        }

        $orderPromotionCode->setProduct($product);
    }
}
