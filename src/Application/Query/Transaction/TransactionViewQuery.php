<?php

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;

class TransactionViewQuery
{
    /** @var Transaction */
    public $transaction;

    /** @var Sheet */
    public $sheet;

    /** @var Payment */
    public $payment;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /**
     * TransactionViewQuery constructor.
     *
     * @param Transaction $transaction
     * @param Sheet       $sheet
     * @param Payment     $payment|null
     * @param Event       $event
     */
    public function __construct(Transaction $transaction, Sheet $sheet, Event $event, Payment $payment = null)
    {
        $this->transaction = $transaction;
        $this->sheet       = $sheet;
        $this->payment     = $payment;
        $this->event       = $event;
        $this->locale      = $sheet->getEvent()->getFallback();
    }
}
