<?php

namespace Proximum\Vimeet\Domain\Payment;

class PaymentConditionsView
{
    /** @var array */
    public $paymentModes;

    /** @var bool */
    public $allowDeposit;

    /** @var \DateTimeInterface */
    public $depositUntil;

    /** @var float */
    public $minimumForDeposit;

    /** @var int */
    public $deposit;

    /**
     * @param array                   $paymentModes
     * @param bool                    $allowDeposit
     * @param \DateTimeInterface|null $depositUntil
     * @param float|null              $minimumForDeposit
     * @param int|null                $deposit
     */
    public function __construct(
        array $paymentModes = [],
        bool $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        float $minimumForDeposit = null,
        int $deposit = null
    ) {
        $this->paymentModes      = $paymentModes;
        $this->allowDeposit      = $allowDeposit;
        $this->depositUntil      = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit           = $deposit;
    }
}
