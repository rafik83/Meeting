<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Transaction;

class Remove implements Command
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
