<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Transaction;

class Update implements Command
{
    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $state;

    /**
     * Create constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->amount      = $transaction->getAmount();
        $this->date        = $transaction->getDate();
        $this->reference   = $transaction->getReference();
        $this->state       = $transaction->getState();
    }
}
