<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Payment;

use Payum\Core\Model\Payment as BasePayment;
use Proximum\Vimeet\Domain\Model\Transaction;

class Payment extends BasePayment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }
}
