<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Transaction".
 */
class Transaction
{
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
     * Transaction constructor.
     *
     * @param Sheet              $sheet
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode
     * @param string             $reference
     */
    public function __construct(Sheet $sheet, $amount, \DateTimeInterface $date, $mode, $reference)
    {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
    }

    /**
     * Update transaction
     *
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode
     * @param string             $reference
     *
     * @return Transaction
     */
    public function update($amount, \DateTimeInterface $date, $mode, $reference)
    {
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;

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
}
