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
     * @var integer
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
     * @param Sheet  $sheet
     * @param float  $amount
     * @param string $mode
     * @param string $reference
     */
    public function __construct(Sheet $sheet, $amount, $mode, $reference)
    {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->mode      = $mode;
        $this->reference = $reference;
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
