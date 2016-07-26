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
     * @var Order[]
     */
    private $orders;

    /**
     * @var Order
     */
    private $orderMerged;

    /**
     * @param array $orders
     *
     * @return Order
     */
    public function merge(array $orders)
    {
        $this->orders = $orders;

        if (count($this->orders) === 0) {
            throw new OrderMergerException();
        }

        if (count($this->orders) === 1) {
            return $this->orders[0];
        }

        /** @var Order $orderPattern */
        $orderPattern = reset($this->orders);

        $this->orderMerged = new Order(
            $orderPattern->getSheet(),
            $orderPattern->isVatApplicable(),
            $orderPattern->getBillingInfo(),
            $orderPattern->getGroupsData(),
            $orderPattern->getCreatedAt()
        );

        foreach ($this->orders as $order) {
            $this->mergeProduct($order);
            $this->mergePromotionCode($order);
        }

        return $this->orderMerged;
    }

    /**
     * @param Order $order
     */
    private function mergeProduct(Order $order)
    {
        foreach ($order->getRows() as $row) {
            if ($orderMergedRow = $this->orderMerged->getRowForProduct($row->getProduct())) {
                $orderMergedRow->setQuantity($orderMergedRow->getQuantity() + $row->getQuantity());
            } else {
                $this->orderMerged->addRow(new Order\Row(
                    $order,
                    $row->getProduct(),
                    $row->getQuantity(),
                    $row->getGroupId()
                ));
            }
        }
    }

    /**
     * @param Order $order
     */
    private function mergePromotionCode(Order $order)
    {
        foreach ($order->getPromotionCodes() as $promotionCode) {
            if (!$this->orderMerged->hasPromotionCode($promotionCode)) {
                $this->orderMerged->addPromotionCode(new Order\PromotionCode(
                    $order,
                    $promotionCode->getPromotionCode(),
                    $promotionCode->getPrice()
                ));
            }
        }
    }
}
