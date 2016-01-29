<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Transaction;

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
     * @var \DateTimeInterface
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
     * Create constructor.
     *
     * @param Sheet              $sheet
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode
     * @param string             $reference
     */
    public function __construct(Sheet $sheet, $amount = null, \DateTimeInterface $date = null, $mode = null, $reference = null)
    {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
    }
}
