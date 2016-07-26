<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class UpdateHandler
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
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $this->transactionRepository->set($update->transaction->update(
            $update->amount,
            $update->date,
            $update->mode,
            $update->reference,
            $update->state
        ));
    }
}
