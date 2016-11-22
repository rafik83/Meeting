<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;

class TransactionConfirmEvent extends AbstractTransactionEvent
{
    /**
     * @var User
     */
    private $user;

    /**
     * TransactionConfirmEvent constructor.
     *
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
