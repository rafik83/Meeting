<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Payment\Mode;

/**
 * "Transaction".
 */
class Transaction
{
    const STATE_PENDING   = 'pending';
    const STATE_PAID      = 'paid';
    const STATE_CANCELLED = 'cancelled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $currency;

    /**
     * Transaction constructor.
     *
     * @param Sheet              $sheet
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode
     * @param null|string        $reference
     * @param string             $state
     * @param string             $currency
     */
    public function __construct(
        Sheet $sheet,
        $amount,
        \DateTimeInterface $date,
        $mode,
        $reference,
        $state,
        $currency
    ) {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
        $this->state     = $state;
        $this->currency  = $currency;
    }

    /**
     * Update transaction
     *
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode
     * @param string             $reference
     * @param string             $state
     *
     * @return Transaction
     */
    public function update($amount, \DateTimeInterface $date, $mode, $reference, $state)
    {
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
        $this->state     = $state;

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get sheet
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get date
     *
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->state === self::STATE_PENDING;
    }

    /**
     * @return bool
     */
    public function isPaypal()
    {
        return Mode::PAYMENT_PAYPAL === $this->getMode();
    }

    /**
     * Set state to Paid
     */
    public function setPaid()
    {
        $this->state = self::STATE_PAID;
    }

    /**
     * Set state to cancelled
     */
    public function setCancelled()
    {
        $this->state = self::STATE_CANCELLED;
    }

    /**
     * @param Sheet              $sheet
     * @param float              $amount
     * @param \DateTimeInterface $date
     *
     * @return Transaction
     */
    public static function createForPaypal(Sheet $sheet, $amount, \DateTimeInterface $date)
    {
        return new self(
            $sheet,
            $amount,
            $date,
            Mode::PAYMENT_PAYPAL,
            null,
            self::STATE_PENDING,
            $sheet->getEvent()->getCurrency()
        );
    }
}
