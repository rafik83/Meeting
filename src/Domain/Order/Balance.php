<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

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
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function getOrders(Sheet $sheet)
    {
        return $this->orderRepository->findBySheet($sheet);
    }

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function getNotCancelledOrders(Sheet $sheet)
    {
        return array_filter($this->getOrders($sheet), function (Order $order) {
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
        return $this->transactionRepository->findBySheet($sheet);
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
}
