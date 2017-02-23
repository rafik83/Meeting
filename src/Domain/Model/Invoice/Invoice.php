<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

/**
 * "Facture"
 */
class Invoice
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $invoicePrefix;

    /**
     * @var string
     */
    private $invoiceYear;

    /**
     * @var string
     */
    private $invoiceIncrement;

    /**
     * @var float
     */
    private $total;

    /**
     * @var float
     */
    private $totalWithVat;

    /**
     * @var float
     */
    private $vatAmount;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Invoice constructor.
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $invoicePrefix
     * @param string $invoiceYear
     * @param string $invoiceIncrement
     * @param float  $total
     * @param float  $totalWithVat
     * @param float  $vatAmount
     * @param \DateTime $createdAt
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        $invoicePrefix,
        $invoiceYear,
        $invoiceIncrement,
        $total,
        $totalWithVat,
        $vatAmount,
        \DateTime $createdAt)
    {
        $this->event            = $event;
        $this->sheet            = $sheet;
        $this->invoicePrefix    = $invoicePrefix;
        $this->invoiceYear      = $invoiceYear;
        $this->invoiceIncrement = $invoiceIncrement;
        $this->total            = $total;
        $this->totalWithVat     = $totalWithVat;
        $this->vatAmount        = $vatAmount;
        $this->createdAt        = $createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getInvoicePrefix()
    {
        return $this->invoicePrefix;
    }

    /**
     * @return string
     */
    public function getInvoiceYear()
    {
        return $this->invoiceYear;
    }

    /**
     * @return string
     */
    public function getInvoiceIncrement()
    {
        return $this->invoiceIncrement;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->getInvoicePrefix() . $this->getInvoiceYear() . '-' . $this->getInvoiceIncrement();
    }
}
