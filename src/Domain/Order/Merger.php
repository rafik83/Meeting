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
use Proximum\Vimeet\Domain\Model\Sheet;

class Merger
{
    /**
     * @param Order[] $orders
     *
     * @return Order
     *
     * @throws OrderMergerException
     */
    public function merge(array $orders)
    {
        if (count($orders) === 0) {
            throw new OrderMergerException();
        }

        /** @var Order|false $firstOrder */
        $firstOrder = reset($orders);

        if (false === $firstOrder) {
            throw new OrderMergerException();
        }

        if (count($orders) === 1) {
            return $firstOrder;
        }

        $orderMerged = new Order(
            $firstOrder->getSheet(),
            $firstOrder->getGroupsData(),
            $firstOrder->getCreatedAt()
        );

        // Merge products and promotion code
        foreach ($orders as $order) {
            $this->mergeProduct($orderMerged, $order);
            $this->mergePromotionCode($orderMerged, $order);
        }

        // Remove products whose merged quantity return 0
        foreach ($orderMerged->getRows() as $row) {
            if ($row->getQuantity() === 0 && null !== $row->getId()) {
                $orderMerged->removeRow($row);
            }
        }

        return $orderMerged;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|Order
     */
    public function getMergedOrders(Sheet $sheet): ?Order
    {
        $orders = $sheet->getNotCancelledOrders();

        if (0 === count($orders)) {
            return null;
        }

        return $this->merge($orders);
    }

    /**
     * @param Order $orderMerged
     * @param Order $order
     */
    private function mergeProduct(Order $orderMerged, Order $order)
    {
        foreach ($order->getRowsWithoutParent() as $row) {
            $orderMergedRow = $orderMerged->getRowForProduct($row->getProduct());

            if (null !== $orderMergedRow) {
                $orderMergedRow->setQuantity($orderMergedRow->getQuantity() + $row->getQuantity());
            } else {
                $cloneRow = clone $row;
                $cloneRow->setOrder($orderMerged);
                $orderMerged->addRow($cloneRow);
            }
        }

        foreach ($order->getRowsWithParent() as $row) {
            $parentRow = $orderMerged->getRowForProduct($row->getParentRow()->getProduct());

            if (null !== $parentRow) {
                $cloneRow = Order\Row::createCustomRowToProduct(
                    $orderMerged,
                    $parentRow,
                    $row->getLabel(),
                    $row->getQuantity(),
                    $row->getPrice()
                );
                $orderMerged->addRow($cloneRow);
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
