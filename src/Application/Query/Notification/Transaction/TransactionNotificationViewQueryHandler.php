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
     * TransactionNotificationViewQueryHandler constructor.
     *
     * @param TransactionRepositoryInterface     $transactionRepository
     * @param TransactionPendingViewQueryHandler $transactionPendingViewQueryHandler
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        TransactionPendingViewQueryHandler $transactionPendingViewQueryHandler
    ) {
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

        $pendingTransactions = $this->transactionRepository->findPending($query->sheet);

        if (count($pendingTransactions) > 0) {
            foreach ($pendingTransactions as $pendingTransaction) {
                $transactionNotificationViews[] = $this->transactionPendingViewQueryHandler->handle(
                    new TransactionPendingViewQuery($pendingTransaction)
                );
            }
        }

        return $transactionNotificationViews;
    }
}
