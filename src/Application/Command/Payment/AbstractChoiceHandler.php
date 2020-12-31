<?php

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

abstract class AbstractChoiceHandler
{
    /** @var Cart\Converter */
    protected $converter;

    /** @var Cart\CartManager */
    protected $cartManager;

    /** @var TotalToPay */
    protected $totalToPay;

    /** @var \DateTimeInterface */
    protected $datetime;

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    /** @var DelayedEventDispatcher */
    protected $eventDispatcher;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Cart\Converter                 $converter
     * @param Cart\CartManager               $cartManager
     * @param TotalToPay                     $totalToPay
     * @param DelayedEventDispatcher         $eventDispatcher
     * @param \DateTimeInterface             $datetime
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        TotalToPay $totalToPay,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->converter             = $converter;
        $this->cartManager           = $cartManager;
        $this->totalToPay            = $totalToPay;
        $this->eventDispatcher       = $eventDispatcher;
        $this->datetime              = $datetime;
    }

    /**
     * @param AbstractChoice $choice
     * @param float          $total
     *
     * @return Transaction
     */
    protected function handleChoice(AbstractChoice $choice, $total): Transaction
    {
        // Convert cart to order
        $order = $this->converter->toOrder($this->cartManager->getCart($choice->sheet));

        // Attached the order to the sheet
        $choice->sheet->addOrder($order);

        $event = new OrderConfirmEvent($order, $choice->user);
        $this->eventDispatcher->dispatch(Events::ORDER_CONFIRMED, $event);

        if (Mode::PAYMENT_PAYPAL === $choice->mode) {
            $transaction = Transaction::createForPaypal(
                $choice->sheet,
                $choice->user,
                $total,
                $this->datetime
            );
        } else {
            $transaction = new Transaction(
                $choice->sheet,
                $total,
                $this->datetime,
                $choice->mode,
                null,
                Transaction::STATE_PENDING,
                $choice->sheet->getEvent()->getCurrency(),
                $choice->user
            );
        }

        $this->transactionRepository->add($transaction);

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_CREATED,
            new TransactionCreatedEvent($transaction)
        );

        return $transaction;
    }
}
