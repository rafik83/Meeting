<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class Balance
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var array
     */
    private $transactions = [];

    /**
     * @var array
     */
    private $orders = [];

    /**
     * @param OrderRepositoryInterface       $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderRepository       = $orderRepository;
        $this->transactionRepository = $transactionRepository;
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
     * @param int[] $sheetIds
     */
    public function loadAllTransactionsForSheetIds(Event $event, array $sheetIds)
    {
        $transactions = $this->transactionRepository->findByEventAndSheetIds($event, $sheetIds);

        foreach ($transactions as $transaction) {
            $this->transactions[$transaction->getSheet()->getId()][] = $transaction;
        }
    }

    /**
     * @param Event $event
     */
    public function loadAllOrders(Event $event)
    {
        $orders = $this->orderRepository->findByEvent($event);

        foreach ($orders as $order) {
            $this->orders[$order->getSheet()->getId()][] = $order;
        }
    }

    /**
     * @param Event $event
     * @param int[] $sheetIds
     */
    public function loadAllOrdersForSheetIds(Event $event, array $sheetIds)
    {
        $orders = $this->orderRepository->findByEventAndSheetIds($event, $sheetIds);

        foreach ($orders as $order) {
            $this->orders[$order->getSheet()->getId()][] = $order;
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
     * @param Event $event
     * @param int[] $sheetIds
     */
    public function loadAllForSheetIds(Event $event, array $sheetIds)
    {
        $this->loadAllOrdersForSheetIds($event, $sheetIds);
        $this->loadAllTransactionsForSheetIds($event, $sheetIds);
    }

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function getOrders(Sheet $sheet)
    {
        if (!isset($this->orders[$sheet->getId()])) {
            $this->orders[$sheet->getId()] = $this->orderRepository->findBySheet($sheet);
        }

        return $this->orders[$sheet->getId()];
    }

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function getNotCancelledOrders(Sheet $sheet)
    {
        $orders = $this->getOrders($sheet);

        return array_filter($orders, function (Order $order) {
            return !$order->isCancelled();
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
     * @return Order[]
     */
    public function getNotCancelledOrdersFromEvent()
    {
        if (!isset($this->orders) || empty($this->orders)) {
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
