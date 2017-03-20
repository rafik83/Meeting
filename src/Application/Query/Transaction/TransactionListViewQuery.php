<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;

class TransactionListViewQuery
{
    /**
     * @var Transaction[]
     */
    public $transactions;
    
    /**
     * TransactionListViewQuery constructor.
     *
     * @param Transaction[] $transactions
     */
    public function __construct(array $transactions)
    {
        $this->transactions = $transactions;
    }
}
