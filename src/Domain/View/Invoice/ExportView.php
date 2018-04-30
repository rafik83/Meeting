<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var string
     */
    public $invoiceDate;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * ExportView constructor.
     *
     * @param int    $eventId
     * @param string $eventTitle
     * @param int    $ownerId
     * @param int    $sheetId
     * @param string $sheetTitle
     * @param string $invoiceNumber
     * @param string $invoiceDate
     * @param int    $total
     * @param int    $totalWithVat
     * @param int    $vatAmount
     * @param int    $balance
     * @param string $analyticsCode
     * @param string $vatNumber
     * @param string $billingInfoCountry
     */
    public function __construct(
        $eventId,
        $eventTitle,
        $ownerId,
        $sheetId,
        $sheetTitle,
        $invoiceNumber,
        $invoiceDate,
        $total,
        $totalWithVat,
        $vatAmount,
        $balance,
        $analyticsCode,
        $vatNumber,
        $billingInfoCountry
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
        $this->vatNumber          = $vatNumber;
        $this->billingInfoCountry = $billingInfoCountry;
        $this->sheetId            = $sheetId;
    }
}
