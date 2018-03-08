<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Type;

use Proximum\Vimeet\Domain\Model\Type;

class PaymentConditions
{
    /** @var int */
    private $id;

    /** @var Type */
    private $type;

    /** @var bool */
    private $allowDeposit = false;

    /** @var \DateTimeInterface|null */
    private $depositUntil;

    /** @var float|null */
    private $minimumForDeposit;

    /** @var int|null */
    private $deposit;

    /** @var array */
    private $paymentModes;

    /**
     * @param Type                    $type
     * @param array                   $paymentModes
     * @param bool                    $allowDeposit
     * @param \DateTimeInterface|null $depositUntil
     * @param float|null              $minimumForDeposit
     * @param int|null                $deposit
     */
    public function __construct(
        Type $type,
        array $paymentModes = [],
        bool $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        float $minimumForDeposit = null,
        int $deposit = null
    ) {
        $this->type              = $type;
        $this->paymentModes      = $paymentModes;
        $this->allowDeposit      = $allowDeposit;
        $this->depositUntil      = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit           = $deposit;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isAllowDeposit(): bool
    {
        return $this->allowDeposit;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDepositUntil()
    {
        return $this->depositUntil;
    }

    /**
     * @return float|null
     */
    public function getMinimumForDeposit()
    {
        return $this->minimumForDeposit;
    }

    /**
     * @return int|null
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @return array
     */
    public function getPaymentModes(): array
    {
        return $this->paymentModes;
    }

    /**
     * @param array                   $paymentModes
     * @param bool                    $allowDeposit
     * @param \DateTimeInterface|null $depositUntil
     * @param float|null              $minimumForDeposit
     * @param int|null                $deposit
     */
    public function update(
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
