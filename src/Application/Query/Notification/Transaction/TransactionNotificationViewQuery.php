<?php

namespace Proximum\Vimeet\Application\Query\Notification\Transaction;

use Proximum\Vimeet\Domain\Model\Sheet;

class TransactionNotificationViewQuery
{
    /** @var Sheet */
    public $sheet;

    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
