<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;

class TransactionPendingViewQuery
{
    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * TransactionPendingViewQuery constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
