<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewQueryHandler
{
    /** @var VatApplicable */
    private $vatApplicable;

    /**
     * @param VatApplicable $vatApplicable
     */
    public function __construct(VatApplicable $vatApplicable)
    {
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param OrderVatViewQuery $orderVatViewQuery
     *
     * @return OrderVatView
     */
    public function handle(OrderVatViewQuery $orderVatViewQuery)
    {
        $order = $orderVatViewQuery->order;

        $isVatApplicable = $this->vatApplicable->onSheet($order->getSheet());

        $totalWithoutVat = AmountFormatter::decimalToCentsAmount($order->getTotalWithoutVat());
        $vatAmount = $isVatApplicable
            ? AmountFormatter::calculateRateAmount($totalWithoutVat, $order->getVatRate())
            : 0;

        $totalWithVat = $totalWithoutVat + $vatAmount;

        return new OrderVatView(
            $order->getNumero(),
            $order->getId(),
            $order->getSheet()->getId(),
            $isVatApplicable,
            $order->getVatRate(),
            $order->getVatMode(),
            $order->getCurrency(),
            $order->isCancelled(),
            $totalWithoutVat,
            $vatAmount,
            $totalWithVat,
            $order->getCreatedAt(),
            $order->getInvoice()
        );
    }
}
