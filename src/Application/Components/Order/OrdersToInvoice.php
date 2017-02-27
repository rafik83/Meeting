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
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class OrdersToInvoice
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Merger */
    private $orderMerger;

    /** @var SummaryQueryHandler */
    private $summaryQueryHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /**
     * @param OrderRepositoryInterface   $orderRepository
     * @param Merger                     $orderMerger
     * @param SummaryQueryHandler        $summaryQueryHandler
     * @param SerializerAdapterInterface $serializerAdapter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Merger $orderMerger,
        SummaryQueryHandler $summaryQueryHandler,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderMerger = $orderMerger;
        $this->summaryQueryHandler = $summaryQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
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

        $view = $this->summaryQueryHandler->handle(new SummaryQuery(
            $sheet,
            $orderMerged,
            $sheet->getEvent()->getFallback()
        ));

        $data = $this->serializerAdapter->serialize($view, 'json');

        return new OrdersToInvoiceView(
            $orders,
            [], // todo : inject merged orders data
            AmountFormatter::decimalToCentimesAmount($orderMerged->getTotalWithoutVat()),
            AmountFormatter::decimalToCentimesAmount($orderMerged->getVatAmount()),
            AmountFormatter::decimalToCentimesAmount($orderMerged->getTotalWithVat())
        );
    }
}
