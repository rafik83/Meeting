<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class CreateHandler
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * CreateHandler constructor.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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
    }
}
