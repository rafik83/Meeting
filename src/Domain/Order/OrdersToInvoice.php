<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class OrdersToInvoice
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Merger */
    private $orderMerger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Merger                   $orderMerger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Merger $orderMerger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderMerger = $orderMerger;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|OrdersToInvoiceView
     */
    public function getOrdersToInvoiceViewForSheet(Sheet $sheet)
    {
        $orders = $this->orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet);

        if (0 === count($orders)) {
            return null;
        }

        $orderMerged = $this->orderMerger->merge($orders);

        if ($orderMerged->getTotal() <= 0) {
            return null;
        }

        return new OrdersToInvoiceView(
            $orders,
            [], // todo : inject merged orders data
            AmountFormatter::decimalToCentimesAmount($orderMerged->getTotalWithoutVat()),
            AmountFormatter::decimalToCentimesAmount($orderMerged->getVatAmount()),
            AmountFormatter::decimalToCentimesAmount($orderMerged->getTotalWithVat())
        );
    }
}
