<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Prefix
     */
    public $prefix;

    /**
     * @var string
     */
    public $invoicePrefix;

    /**
     * @var int
     */
    public $invoiceYear;

    /**
     * @var int
     */
    public $invoiceNumber;

    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $totalWithVat;

    /**
     * @var int
     */
    public $vatAmount;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * Create constructor.
     *
     * @param Event              $event
     * @param Sheet              $sheet
     * @param Prefix             $prefix
     * @param string             $invoicePrefix
     * @param string             $invoiceYear
     * @param string             $invoiceNumber
     * @param float              $total
     * @param float              $totalWithVat
     * @param float              $vatAmount
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        Prefix $prefix,
        $invoicePrefix,
        $invoiceYear,
        $invoiceNumber,
        $total,
        $totalWithVat,
        $vatAmount,
        \DateTimeInterface $createdAt
    )
    {
        $this->event         = $event;
        $this->sheet         = $sheet;
        $this->prefix        = $prefix;
        $this->invoicePrefix = $invoicePrefix;
        $this->invoiceYear   = $invoiceYear;
        $this->invoiceNumber = $invoiceNumber;
        $this->total         = $total;
        $this->totalWithVat  = $totalWithVat;
        $this->vatAmount     = $vatAmount;
        $this->createdAt     = $createdAt;
    }
}
