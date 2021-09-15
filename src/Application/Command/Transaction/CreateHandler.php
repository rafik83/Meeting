<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CreateHandler
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
     * CreateHandler constructor.
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $transaction = new Transaction(
            $create->sheet,
            $create->amount,
            $create->date,
            $create->mode,
            $create->reference,
            $create->state,
            $create->sheet->getEvent()->getCurrency()
        );

        $this->transactionRepository->add($transaction);

        if ($transaction->isPaid()) {
            $event = new TransactionConfirmedEvent($transaction->getUser(), $transaction);
            $this->eventDispatcher->dispatch(Events::TRANSACTION_CONFIRMED, $event);
        }

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_CREATED,
            new TransactionCreatedEvent($transaction)
        );
    }
}
