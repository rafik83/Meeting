<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateHandler
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
     * UpdateHandler constructor.
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
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $wasNotPaid = !$update->transaction->isPaid();

        $this->transactionRepository->set($update->transaction->update(
            $update->amount,
            $update->date,
            $update->reference,
            $update->state
        ));

        // if transaction was not paid and now it is paid
        // then send a notification to that user
        if ($wasNotPaid && Transaction::STATE_PAID === $update->state) {
            $event = new TransactionConfirmedEvent($update->transaction->getUser(), $update->transaction);
            $this->eventDispatcher->dispatch(Events::TRANSACTION_CONFIRMED, $event);
        }

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_UPDATED,
            new TransactionUpdatedEvent($update->transaction)
        );
    }
}
