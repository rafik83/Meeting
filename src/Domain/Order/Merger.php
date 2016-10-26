<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Exception\Order\OrderMergerException;
use Proximum\Vimeet\Domain\Model\Order;

class Merger
{
    /**
     * @param array $orders
     *
     * @return Order
     */
    public function merge(array $orders)
    {
        if (count($orders) === 0) {
            throw new OrderMergerException();
        }

        if (count($orders) === 1) {
            return reset($orders);
        }

        /** @var Order $orderPattern */
        $orderPattern = reset($orders);

        $orderMerged = new Order(
            $orderPattern->getSheet(),
            $orderPattern->isVatApplicable(),
            $orderPattern->getBillingInfo(),
            $orderPattern->getGroupsData(),
            $orderPattern->getCreatedAt()
        );

        foreach ($orders as $order) {
            $this->mergeProduct($orderMerged, $order);
            $this->mergePromotionCode($orderMerged, $order);
        }

        return $orderMerged;
    }

    /**
     * @param Order $orderMerged
     * @param Order $order
     */
    private function mergeProduct(Order $orderMerged, Order $order)
    {
        foreach ($order->getRows() as $row) {
            if (null !== ($orderMergedRow = $orderMerged->getRowForProduct($row->getProduct()))) {
                $orderMergedRow->setQuantity($orderMergedRow->getQuantity() + $row->getQuantity());
            } else {
                $cloneRow = clone $row;
                $orderMerged->addRow($cloneRow->setOrder($orderMerged));
            }
        }
    }

    /**
     * @param Order $orderMerged
     * @param Order $order
     */
    private function mergePromotionCode(Order $orderMerged, Order $order)
    {
        foreach ($order->getPromotionCodes() as $promotionCode) {
            if (!$orderMerged->hasPromotionCode($promotionCode)) {
                $promotionCodeClone = clone $promotionCode;
                $orderMerged->addPromotionCode($promotionCodeClone->setOrder($orderMerged));
            }
        }
    }
}
