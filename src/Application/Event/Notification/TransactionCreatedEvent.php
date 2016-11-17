<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Notification;

use Proximum\Vimeet\Domain\Model\Transaction;
use Symfony\Component\EventDispatcher;

class TransactionCreatedEvent extends EventDispatcher\Event
{
    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * TransactionCreatedEvent constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
