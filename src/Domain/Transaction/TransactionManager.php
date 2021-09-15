<?php

namespace Proximum\Vimeet\Domain\Transaction;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class TransactionManager
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->transactionRepository  = $transactionRepository;
        $this->eventDispatcher        = $eventDispatcher;
    }

    /**
     * @param Transaction $transaction
     */
    public function setPaid(Transaction $transaction)
    {
        $transaction->setPaid();
        $transaction->unHide();
        $this->transactionRepository->set($transaction);

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_CONFIRMED,
            new TransactionConfirmedEvent($transaction->getUser(), $transaction)
        );
    }
}
