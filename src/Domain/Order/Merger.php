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
     * @var Order[]
     */
    private $orders;

    /**
     * @var Order
     */
    private $orderMerged;

    /**
     * Merger constructor.
     *
     * @param Sheet   $sheet
     * @param Order[] $orders
     */
    public function __construct(Sheet $sheet, array $orders)
    {
        if (count($orders) === 0) {
            throw new OrderMergerException();
        }

        $this->orders = $orders;

        /** @var Order $orderPattern */
        $orderPattern = reset($orders);

        $this->orderMerged = new Order(
            $orderPattern->getSheet(),
            $orderPattern->isVatApplicable(),
            $orderPattern->getBillingInfo(),
            $sheet->getPackage()->serializeData(),
            $orderPattern->getCreatedAt()
        );
    }

    /**
     * @return Order
     */
    public function merge()
    {
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
