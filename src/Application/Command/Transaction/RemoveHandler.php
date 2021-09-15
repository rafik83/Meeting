<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionRemovedEvent;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveHandler
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
     * RemoveHandler constructor.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->eventDispatcher       = $eventDispatcher;
    }

    /**
     * @param Remove $remove
     */
    public function handle(Remove $remove)
    {
        $this->transactionRepository->remove($remove->transaction);

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_REMOVED,
            new TransactionRemovedEvent($remove->transaction)
        );
    }
}
