<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Order;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQuery;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class OrdersToInvoice
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Merger */
    private $orderMerger;

    /** @var InvoiceDataQueryHandler */
    private $invoiceDataQueryHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var VatApplicable */
    private $vatApplicable;

    /**
     * @param OrderRepositoryInterface   $orderRepository
     * @param Merger                     $orderMerger
     * @param InvoiceDataQueryHandler    $invoiceDataQueryHandler
     * @param VatApplicable              $vatApplicable
     * @param SerializerAdapterInterface $serializerAdapter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Merger $orderMerger,
        InvoiceDataQueryHandler $invoiceDataQueryHandler,
        VatApplicable $vatApplicable,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->orderRepository          = $orderRepository;
        $this->orderMerger              = $orderMerger;
        $this->invoiceDataQueryHandler  = $invoiceDataQueryHandler;
        $this->serializerAdapter        = $serializerAdapter;
        $this->vatApplicable            = $vatApplicable;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|OrdersToInvoiceView
     */
    public function getOrdersToInvoiceViewForSheet(Sheet $sheet)
    {
        $orders = $this->orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet);

        if (0 === \count($orders)) {
            return null;
        }

        $orderMerged = $this->orderMerger->merge($orders);

        if ($orderMerged->getTotalWithoutVat() <= 0) {
            return null;
        }

        $vatToPay = 0;
        $isVatApplicable = $this->vatApplicable->onSheet($sheet);

        $total = AmountFormatter::decimalToCentsAmount($orderMerged->getTotalWithoutVat());
        $vatRate = $sheet->getEvent()->getVat();

        if (true === $isVatApplicable) {
            $vatToPay = AmountFormatter::calculateRateAmount($total, $vatRate);
        }

        $view = $this->invoiceDataQueryHandler->handle(
            new InvoiceDataQuery(
                $orderMerged->getSheet(),
                $orderMerged,
                $orderMerged->getSheet()->getEvent()->getFallback()
            )
        );

        $data = $this->serializerAdapter->serialize($view, 'json');

        $event = $sheet->getEvent();

        return new OrdersToInvoiceView(
            $orders,
            $data,
            $isVatApplicable,
            $event->getMode(),
            $event->getVat(),
            $total,
            $vatToPay,
            $total + $vatToPay,
            $orderMerged->getCurrency()
        );
    }
}
