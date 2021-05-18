<?php

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Domain\Model\Transaction;

class PaymentResult
{
    public Transaction $transaction;
    public int $orderId;

    public function __construct(Transaction $transaction, int $orderId)
    {
        $this->transaction = $transaction;
        $this->orderId = $orderId;
    }
}
