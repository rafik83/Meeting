<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class OrderListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $paymentMode;

    /**
     * OrderListView constructor.
     *
     * @param int                $id
     * @param string             $reference
     * @param \DateTimeInterface $date
     * @param float              $amount
     * @param string             $state
     * @param string             $paymentMode
     */
    public function __construct($id, $reference, \DateTimeInterface $date, $amount, $state, $paymentMode)
    {
        $this->id          = $id;
        $this->reference   = $reference;
        $this->date        = $date;
        $this->amount      = $amount;
        $this->state       = $state;
        $this->paymentMode = $paymentMode;
    }
}
