<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Invoice;

class ExportView
{
    /**
     * @var int
     */
    public $eventId;

    /**
     * @var string
     */
    public $eventTitle;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * @var string
     */
    public $sheetTitle;

    /**
     * @var string
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
     * @var int
     */
    public $balance;

    /**
     * @var string
     */
    public $billingInfoCountry;

    /**
     * @var string
     */
    public $vatNumber;

    /**
     * @var string
     */
    public $analyticsCode;

    /**
     * @var \DateTimeInterface
     */
    public $invoiceDate;

    /**
     * ExportView constructor.
     *
     * @param int       $eventId
     * @param string    $eventTitle
     * @param int       $ownerId
     * @param string    $sheetTitle
     * @param string    $invoiceNumber
     * @param \DateTime $invoiceDate
     * @param int       $total
     * @param int       $totalWithVat
     * @param int       $vatAmount
     * @param int       $balance
     * @param string    $analyticsCode
     */
    public function __construct(
        $eventId,
        $eventTitle,
        $ownerId,
        $sheetTitle,
        $invoiceNumber,
        $invoiceDate,
        $total,
        $totalWithVat,
        $vatAmount,
        $balance,
        $analyticsCode
    ) {
        $this->eventId            = $eventId;
        $this->eventTitle         = $eventTitle;
        $this->ownerId            = $ownerId;
        $this->sheetTitle         = $sheetTitle;
        $this->invoiceNumber      = $invoiceNumber;
        $this->total              = $total;
        $this->totalWithVat       = $totalWithVat;
        $this->vatAmount          = $vatAmount;
        $this->balance            = $balance;
        $this->analyticsCode      = $analyticsCode;
        $this->invoiceDate        = $invoiceDate;
    }
}
