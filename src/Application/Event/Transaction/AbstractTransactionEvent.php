<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;
use Symfony\Component\EventDispatcher;

abstract class AbstractTransactionEvent extends EventDispatcher\Event
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * TransactionUpdatedEvent constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
