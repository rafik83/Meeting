<?php

namespace Proximum\Vimeet\Application\Event\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmedEvent extends AbstractTransactionEvent
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User        $user
     * @param Transaction $transaction
     */
    public function __construct(User $user, Transaction $transaction)
    {
        parent::__construct($transaction);

        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
