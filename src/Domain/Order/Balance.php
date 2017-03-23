<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class Balance
{
    /** @var OrderVatViewsByEventQueryHandler */
    private $orderVatViewsByEventQueryHandler;

    /** @var OrderVatViewsBySheetQueryHandler */
    private $orderVatViewsBySheetQueryHandler;

    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var OrderVatView[] */
    private $orderVatViews = [];

    /** @var Transaction[] */
    private $transactions = [];

    /**
     * @param OrderVatViewsByEventQueryHandler $orderVatViewsByEventQueryHandler
     * @param OrderVatViewsBySheetQueryHandler $orderVatViewsBySheetQueryHandler
     * @param TransactionRepositoryInterface   $transactionRepository
     */
    public function __construct(
        OrderVatViewsByEventQueryHandler $orderVatViewsByEventQueryHandler,
        OrderVatViewsBySheetQueryHandler $orderVatViewsBySheetQueryHandler,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderVatViewsByEventQueryHandler = $orderVatViewsByEventQueryHandler;
        $this->orderVatViewsBySheetQueryHandler = $orderVatViewsBySheetQueryHandler;
        $this->transactionRepository            = $transactionRepository;
    }

    /**
     * @param Event $event
     */
    public function loadAllTransactions(Event $event)
    {
        $transactions = $this->transactionRepository->findByEvent($event);

        foreach ($transactions as $transaction) {
            $this->transactions[$transaction->getSheet()->getId()][] = $transaction;
        }
    }

    /**
     * @param Event $event
     */
    public function loadAllOrders(Event $event)
    {
        $orderVatViews = $this->orderVatViewsByEventQueryHandler->handle(new OrderVatViewsByEventQuery($event));

        foreach ($orderVatViews as $orderVatView) {
            $this->orderVatViews[$orderVatView->sheetId][] = $orderVatView;
        }
    }

    /**
     * @param Event $event
     */
    public function loadAllForEvent(Event $event)
    {
        $this->loadAllOrders($event);
        $this->loadAllTransactions($event);
    }

    /**
     * @param Sheet $sheet
     *
     * @return OrderVatView[]
     */
    public function getOrders(Sheet $sheet)
    {
        if (!isset($this->orderVatViews[$sheet->getId()])) {
            $this->orderVatViews[$sheet->getId()] = $this->orderVatViewsBySheetQueryHandler->handle(
                new OrderVatViewsBySheetQuery($sheet)
            );
        }

        return $this->orderVatViews[$sheet->getId()];
    }

    /**
     * @param Sheet $sheet
     *
     * @return OrderVatView[]
     */
    public function getNotCancelledOrders(Sheet $sheet)
    {
        $orderVatViews = $this->getOrders($sheet);

        return array_filter($orderVatViews, function (OrderVatView $orderVatView) {
            return !$orderVatView->isCancelled;
        });
    }

    /**
     * @param Sheet $sheet
     *
     * @return Transaction[]
     */
    public function getTransactions(Sheet $sheet)
    {
        if (!isset($this->transactions[$sheet->getId()])) {
            $this->transactions[$sheet->getId()] = $this->transactionRepository->findBySheet($sheet);
        }

        return $this->transactions[$sheet->getId()];
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getTotal(Sheet $sheet)
    {
        $orders = $this->getNotCancelledOrders($sheet);


        $total = 0;

        foreach ($orders as $order) {
            $total += $order->getTotal();
        }

        return $total;
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getTotalWithoutVat(Sheet $sheet)
    {
        $orders = $this->getNotCancelledOrders($sheet);

        $totalWithoutVat = 0;

        foreach ($orders as $order) {
            $totalWithoutVat += $order->getTotalWithoutVat();
        }

        return $totalWithoutVat;
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getBalance(Sheet $sheet)
    {
        return $this->getTotal($sheet) - $this->getTotalPaid($sheet);
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getRemainingToPay(Sheet $sheet)
    {
        $remainingToPay = $this->getBalance($sheet);

        if ($remainingToPay < 0) {
            return 0;
        }

        return $remainingToPay;
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getTotalPaid(Sheet $sheet)
    {
        $totalPaid    = 0;
        $transactions = $this->getTransactions($sheet);

        foreach ($transactions as $transaction) {
            if ($transaction->isPaid()) {
                $totalPaid += $transaction->getAmount();
            }
        }

        return $totalPaid;
    }

    /**
     * @return OrderVatView[]
     */
    public function getNotCancelledOrdersFromEvent()
    {
        if (!isset($this->or) || empty($this->orders)) {
            return [];
        }

        $notCancelledOrdersFromEvent = [];

        foreach ($this->orders as $sheetOrders) {
            /** @var Order $order */
            foreach ($sheetOrders as $order) {
                if (!$order->isCancelled()) {
                    $notCancelledOrdersFromEvent[] = $order;
                }
            }
        }

        return $notCancelledOrdersFromEvent;
    }

    /**
     * @return float
     */
    public function getTransactionsTotalPaidForEvent()
    {
        $totalPaid = 0;

        foreach ($this->transactions as $sheetTransactions) {
            /** @var Transaction $transaction */
            foreach ($sheetTransactions as $transaction) {
                if ($transaction->isPaid()) {
                    $totalPaid += $transaction->getAmount();
                }
            }
        }

        return $totalPaid;
    }

    /**
     * @return float
     */
    public function getOrdersTotalForEvent()
    {
        $orders = $this->getNotCancelledOrdersFromEvent();

        $total = 0;

        foreach ($orders as $order) {
            $total += $order->getTotal();
        }

        return $total;
    }

    /**
     * @return float
     */
    public function getOrdersTotalRemainingToPayForEvent()
    {
        $remainingToPay = $this->getOrdersTotalForEvent() - $this->getTransactionsTotalPaidForEvent();

        if (0 > $remainingToPay) {
            return 0;
        }

        return $remainingToPay;
    }
}
