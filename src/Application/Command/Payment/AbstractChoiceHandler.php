<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Domain\Cart;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

abstract class AbstractChoiceHandler
{
    /**
     * @var Cart\Converter
     */
    protected $converter;

    /**
     * @var Cart\CartManager
     */
    protected $cartManager;

    /**
     * @var TotalToPay
     */
    protected $totalToPay;

    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Cart\Converter                 $converter
     * @param Cart\CartManager               $cartManager
     * @param TotalToPay                     $totalToPay
     * @param \DateTimeInterface             $datetime
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        Cart\Converter $converter,
        Cart\CartManager $cartManager,
        TotalToPay $totalToPay,
        \DateTimeInterface $datetime
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->converter             = $converter;
        $this->cartManager           = $cartManager;
        $this->totalToPay            = $totalToPay;
        $this->datetime              = $datetime;
    }

    /**
     * @param AbstractChoice $choice
     * @param float          $total
     *
     * @return Transaction
     * @throws MissingBillingInfoException
     */
    protected function handleChoice(AbstractChoice $choice, $total)
    {
        // Convert cart to order
        $this->converter->toOrder($this->cartManager->getCart($choice->sheet));

        if (Mode::PAYMENT_PAYPAL === $choice->mode) {
            $transaction = Transaction::createForPaypal($choice->sheet, $total, $this->datetime);
        } else {
            $transaction = new Transaction(
                $choice->sheet,
                $total,
                $this->datetime,
                $choice->mode,
                null,
                Transaction::STATE_PENDING,
                $choice->sheet->getEvent()->getCurrency()
            );
        }

        $this->transactionRepository->add($transaction);

        return $transaction;
    }
}
