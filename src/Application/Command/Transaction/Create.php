<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class Create
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var DateTimeInterface
     */
    public $date;

    /**
     * @var string
     */
    public $mode;

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
     * @param Sheet             $sheet
     * @param null|string       $state
     * @param null|float        $amount
     * @param DateTimeInterface $date
     * @param null|string       $mode
     * @param null|string       $reference
     */
    public function __construct(
        Sheet $sheet,
        $amount = null,
        DateTimeInterface $date = null,
        $mode = null,
        $reference = null,
        $state = null
    ) {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
        $this->state     = $state;
    }
}
