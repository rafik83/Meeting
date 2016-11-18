<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class TransactionNotificationViewQueryHandler
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var TransactionPendingViewQueryHandler
     */
    private $transactionPendingViewQueryHandler;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * TransactionNotificationViewQueryHandler constructor.
     *
     * @param Balance                            $balance
     * @param TransactionRepositoryInterface     $transactionRepository
     * @param TransactionPendingViewQueryHandler $transactionPendingViewQueryHandler
     */
    public function __construct(
        Balance $balance,
        TransactionRepositoryInterface $transactionRepository,
        TransactionPendingViewQueryHandler $transactionPendingViewQueryHandler
    ) {
        $this->balance                            = $balance;
        $this->transactionRepository              = $transactionRepository;
        $this->transactionPendingViewQueryHandler = $transactionPendingViewQueryHandler;
    }

    /**
     * @param TransactionNotificationViewQuery $query
     *
     * @return NotificationView[]
     */
    public function handle(TransactionNotificationViewQuery $query)
    {
        $transactionNotificationViews = [];

        $balance             = $this->balance->getBalance($query->sheet);
        $pendingTransactions = $this->transactionRepository->findPending($query->sheet);

        // generate notification if transaction pending and balance is positive
        if (count($pendingTransactions) > 0 && $balance > 0) {
            foreach ($pendingTransactions as $pendingTransaction) {
                $transactionNotificationViews[] = $this->transactionPendingViewQueryHandler->handle(
                    new TransactionPendingViewQuery($pendingTransaction)
                );
            }
        }

        return $transactionNotificationViews;
    }
}
