<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
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
     *
     * @throws MissingBillingInfoException
     */
    public function handle(OrderVatViewQuery $orderVatViewQuery): OrderVatView
    {
        $order = $orderVatViewQuery->order;

        $isVatApplicable = $this->vatApplicable->onSheet($order->getSheet());
        $totalWithoutVat = AmountFormatter::decimalToCentsAmount($order->getTotalWithoutVat());

        $vatViews = [];
        foreach ($order->getRows() as $row) {
            $this->addToVatViews(
                $vatViews,
                $row->getVatRate(),
                $row->getPrice() * $row->getQuantity(),
                $order->getVatMode()
            );
        }

        foreach ($order->getPromotionCodes() as $promotionCodeRow) {
            $this->addToVatViews(
                $vatViews,
                $promotionCodeRow->getVatRate(),
                $promotionCodeRow->getPrice(),
                $order->getVatMode()
            );
        }

        $vatAmount = 0;

        foreach ($vatViews as $vatView) {
            $vatAmount += $vatView->totalVat;
        }

        $totalWithVat = $vatAmount + $totalWithoutVat;

        $vatListView = new VatListView(
            $totalWithoutVat,
            $totalWithVat,
            $isVatApplicable,
            $order->getVatMode(),
            $vatViews
        );

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
            $vatListView->totalWithVat,
            $vatListView,
            $order->getCreatedAt(),
            $order->getInvoice()
        );
    }

    /**
     * @param array  $vatViews
     * @param float  $vatRate
     * @param float  $price
     * @param string $vatMode
     */
    private function addToVatViews(array &$vatViews, float $vatRate, float $price, string $vatMode): void
    {
        $index = 'vat_' . $vatRate;
        if (!isset($vatViews[$index])) {
            $vatViews[$index] = new VatView($vatRate, $vatMode, 0, 0);
        }

        $vatViews[$index]->addToTotal(AmountFormatter::decimalToCentsAmount($price));
    }
}
