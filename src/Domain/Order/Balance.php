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
        $this->orderRepository        = $orderRepository;
        $this->transactionRepository  = $transactionRepository;
    }

    /**
     * @param Event $event
     */
    public function loadAllTransactions(Event $event)
    {
        $this->transactions[$event->getId()] = $this->transactionRepository->findByEvent($event);
    }

    /**
     * @param Event $event
     */
    public function loadAllOrders(Event $event)
    {
        $orders = $this->orderRepository->findByEvent($event);

        foreach ($orders as $order) {
            $this->orders[$event->getId()][$order->getSheet()->getId()] = $order;
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function getOrders(Sheet $sheet)
    {
        if (!isset($this->orders[$sheet->getEvent()->getId()][$sheet->getId()])) {
            $this->orders[$sheet->getEvent()->getId()][$sheet->getId()] = $this->orderRepository->findBySheet($sheet);
        }

        return $this->orders[$sheet->getEvent()->getId()][$sheet->getId()];
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
        if (!isset($this->transactions[$sheet->getEvent()->getId()][$sheet->getId()])) {
            $this->transactions[$sheet->getEvent()->getId()][$sheet->getId()] = $this->transactionRepository->findBySheet($sheet);
        }

        return $this->transactions[$sheet->getEvent()->getId()][$sheet->getId()];
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getTotal(Sheet $sheet)
    {
        $orders = $this->getNotCancelledOrders($sheet);

        return array_reduce($orders, function ($carry, Order $order) {
            return $carry + $order->getTotal();
        }, 0);
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getBalance(Sheet $sheet)
    {
        $total        = $this->getTotal($sheet);
        $transactions = $this->getTransactions($sheet);

        return array_reduce($transactions, function ($carry, Transaction $transaction) {
            if (!$transaction->isPaid()) {
                return $carry;
            }

            return $carry - $transaction->getAmount();
        }, $total);
    }

    /**
     * @param Sheet $sheet
     *
     * @return float
     */
    public function getRemainingToPay(Sheet $sheet)
    {
        $total        = $this->getTotal($sheet);
        $transactions = $this->getTransactions($sheet);

        return array_reduce($transactions, function ($carry, Transaction $transaction) {
            if ($carry < 0) {
                return 0;
            }

            if (!$transaction->isPaid()) {
                return $carry;
            }

            if (($carry - $transaction->getAmount()) < 0) {
                return 0;
            }

            return $carry - $transaction->getAmount();
        }, $total);
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
     * @param Event $event
     *
     * @return array
     */
    public function getNotCancelledOrdersFromEvent(Event $event)
    {
        if (!isset($this->orders[$event->getId()])) {
            return [];
        }

        return array_filter($this->orders[$event->getId()], function (Order $order) {
            return !$order->isCancelled();
        });
    }

    /**
     * @param Event $event
     *
     * @return float
     */
    public function getTransactionsTotalPaid(Event $event)
    {
        $totalPaid = 0;

        foreach ($this->transactions[$event->getId()] as $transaction) {
            if ($transaction->isPaid()) {
                $totalPaid += $transaction->getAmount();
            }
        }

        return $totalPaid;
    }

    /**
     * @param Event $event
     *
     * @return float
     */
    public function getOrdersTotal(Event $event)
    {
        $orders = $this->getNotCancelledOrdersFromEvent($event);

        return array_reduce($orders, function ($carry, Order $order) {
            return $carry + $order->getTotal();
        }, 0);
    }

    /**
     * @param Event $event
     *
     * @return float
     */
    public function getOrdersTotalRemainingToPay(Event $event)
    {
        $total = $this->getOrdersTotal($event);

        return array_reduce($this->transactions[$event->getId()], function ($carry, Transaction $transaction) {
            if ($carry < 0) {
                return 0;
            }

            if (!$transaction->isPaid()) {
                return $carry;
            }

            if (($carry - $transaction->getAmount()) < 0) {
                return 0;
            }

            return $carry - $transaction->getAmount();
        }, $total);
    }
}
