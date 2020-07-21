<?php

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class TransactionNotificationViewQueryHandler
{
    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var TransactionPendingViewQueryHandler */
    private $transactionPendingViewQueryHandler;

    /** @var Balance */
    private $balance;

    /** @var TransactionPaidViewQueryHandler */
    private $transactionPaidViewQueryHandler;

    public function __construct(
        Balance $balance,
        TransactionRepositoryInterface $transactionRepository,
        TransactionPendingViewQueryHandler $transactionPendingViewQueryHandler,
        TransactionPaidViewQueryHandler $transactionPaidViewQueryHandler
    ) {
        $this->balance                            = $balance;
        $this->transactionRepository              = $transactionRepository;
        $this->transactionPendingViewQueryHandler = $transactionPendingViewQueryHandler;
        $this->transactionPaidViewQueryHandler    = $transactionPaidViewQueryHandler;
    }

    /**
     * @param TransactionNotificationViewQuery $query
     *
     * @return NotificationView[]
     */
    public function handle(TransactionNotificationViewQuery $query): array
    {
        $transactionNotificationViews = [];

        $balance = $this->balance->getBalance($query->sheet);
        $pendingTransactions = $this->transactionRepository->findPending($query->sheet);
        $paidTransactions = $this->transactionRepository->findPaid($query->sheet);

        // generate notification if transaction pending and balance is positive
        if (count($pendingTransactions) > 0 && $balance > 0) {
            foreach ($pendingTransactions as $pendingTransaction) {
                $transactionNotificationViews[] = $this->transactionPendingViewQueryHandler->handle(
                    new TransactionPendingViewQuery($pendingTransaction)
                );
            }
        }

        if (count($paidTransactions) > 0) {
            foreach ($paidTransactions as $paidTransaction) {
                $transactionNotificationViews[] = $this->transactionPaidViewQueryHandler->handle(
                    new TransactionPaidViewQuery($paidTransaction)
                );
            }
        }

        return $transactionNotificationViews;
    }
}
