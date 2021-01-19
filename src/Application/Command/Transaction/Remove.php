<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;

class Remove
{
    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * Remove constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
