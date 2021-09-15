<?php

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;

class TransactionPendingViewQuery
{
    /** @var Transaction */
    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
