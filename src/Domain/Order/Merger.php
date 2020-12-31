<?php

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Exception\Order\OrderMergerException;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Sheet;

class Merger
{
    /**
     * @param Order[] $orders
     *
     * @throws OrderMergerException
     *
     * @return Order
     */
    public function merge(array $orders): Order
    {
        if (0 === \count($orders)) {
            throw new OrderMergerException('There is no order to merge');
        }

        /** @var Order|false $firstOrder */
        $firstOrder = reset($orders);

        if (false === $firstOrder) {
            throw new OrderMergerException('There is no order to merge');
        }

        if (1 === \count($orders)) {
            return $firstOrder;
        }

        $orderMerged = new Order(
            $firstOrder->getSheet(),
            '[]',
            $firstOrder->getCreatedAt()
        );

        $orderMerged->setGroups($this->mergeGroupsData($orders));

        // Merge products and promotion code
        foreach ($orders as $order) {
            $this->mergeProduct($orderMerged, $order);
            $this->mergePromotionCode($orderMerged, $order);
        }

        // Remove products whose merged quantity return 0
        foreach ($orderMerged->getRows() as $row) {
            if (0 === $row->getQuantity() && !$row->isCustomRow()) {
                $this->detachChildRow($orderMerged, $row);
                $orderMerged->removeRow($row);
            }
        }

        return $orderMerged;
    }

    /**
     * @param Sheet $sheet
     *
     * @throws OrderMergerException
     *
     * @return null|Order
     */
    public function getMergedOrders(Sheet $sheet): ?Order
    {
        $orders = $sheet->getNotCancelledOrders();

        if (0 === \count($orders)) {
            return null;
        }

        return $this->merge($orders);
    }

    private function detachChildRow(Order $orderMerged, Row $parentRow): void
    {
        foreach ($orderMerged->getRows() as $row) {
            if ($parentRow === $row->getParentRow()) {
                $row->detachParentRow();
            }
        }
    }

    /**
     * @param Order $orderMerged
     * @param Order $order
     */
    private function mergeProduct(Order $orderMerged, Order $order): void
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
                    $row->getPrice(),
                    $row->getVatRate()
                );
                $orderMerged->addRow($cloneRow);
            }
        }
    }

    /**
     * @param Order $orderMerged
     * @param Order $order
     */
    private function mergePromotionCode(Order $orderMerged, Order $order): void
    {
        foreach ($order->getPromotionCodes() as $promotionCode) {
            if (!$orderMerged->hasPromotionCode($promotionCode)) {
                $promotionCodeClone = clone $promotionCode;
                $orderMerged->addPromotionCode($promotionCodeClone->setOrder($orderMerged));
            }
        }
    }

    /**
     * @param Order[] $orders
     *
     * @return array
     */
    private function mergeGroupsData(array $orders): array
    {
        $groups = [];

        foreach ($orders as $order) {
            foreach ($order->getGroups() as $id => $group) {
                $groups[$id] = $group;
            }
        }

        return $groups;
    }
}
