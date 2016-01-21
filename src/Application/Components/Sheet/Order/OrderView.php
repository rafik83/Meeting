<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

class OrderView extends Groups
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
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $paymentMode;

    /**
     * OrderView constructor.
     *
     * @param int                $id
     * @param string             $reference
     * @param \DateTimeInterface $date
     * @param string             $state
     * @param string             $paymentMode
     * @param float              $vat
     * @param GroupView[]        $groups
     */
    public function __construct($id, $reference, \DateTimeInterface $date, $state, $paymentMode, $vat, array $groups = [])
    {
        parent::__construct($groups, $vat);

        $this->id          = $id;
        $this->reference   = $reference;
        $this->date        = $date;
        $this->state       = $state;
        $this->paymentMode = $paymentMode;
    }
}
