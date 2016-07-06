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
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class ChoiceHandler
{
    /**
     * @var Cart\Converter
     */
    private $converter;

    /**
     * @var Cart\CartManager
     */
    private $cartManager;

    /**
     * @var TotalToPay
     */
    private $totalToPay;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

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
     * @param Choice $choice
     */
    public function handle(Choice $choice)
    {
        $total = $this->totalToPay->getTotal($choice->sheet);

        if (Mode::isDeposit($choice->mode)) {
            $total = DepositApplicable::calculateDeposit($choice->sheet->getEvent(), new \DateTime(), $total);
        }

        // Convert cart to order
        $this->converter->toOrder($this->cartManager->getCart($choice->sheet));

        // Create Transaction
        $transaction = new Transaction(
            $choice->sheet,
            $total,
            $this->datetime,
            $choice->mode,
            null,
            Transaction::STATE_PENDING
        );

        $this->transactionRepository->add($transaction);
    }
}
