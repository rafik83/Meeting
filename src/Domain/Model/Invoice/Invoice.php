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
     * @var Prefix
     */
    private $prefix;

    /**
     * @var string
     */
    private $invoicePrefix;

    /**
     * @var int
     */
    private $invoiceYear;

    /**
     * @var int
     */
    private $invoiceIncrement;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $totalWithVat;

    /**
     * @var int
     */
    private $vatAmount;

    /**
     * @var string 3-letter ISO 4217 currency name
     */
    private $currency;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $data;

    /**
     * Invoice constructor.
     *
     * @param Event              $event
     * @param Sheet              $sheet
     * @param Prefix             $prefix
     * @param string             $invoicePrefix
     * @param int                $invoiceYear
     * @param int                $invoiceIncrement
     * @param int                $total
     * @param int                $totalWithVat
     * @param int                $vatAmount
     * @param string             $currency
     * @param string             $data
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        Prefix $prefix,
        $invoicePrefix,
        $invoiceYear,
        $invoiceIncrement,
        $total,
        $totalWithVat,
        $vatAmount,
        $currency,
        $data,
        \DateTimeInterface $createdAt
    ) {
        $this->event            = $event;
        $this->sheet            = $sheet;
        $this->prefix           = $prefix;
        $this->invoicePrefix    = $invoicePrefix;
        $this->invoiceYear      = $invoiceYear;
        $this->invoiceIncrement = $invoiceIncrement;
        $this->total            = $total;
        $this->totalWithVat     = $totalWithVat;
        $this->vatAmount        = $vatAmount;
        $this->currency         = $currency;
        $this->data             = $data;
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
     * @return Prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return int
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return \DateTimeInterface
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
     * @return int
     */
    public function getInvoiceYear()
    {
        return $this->invoiceYear;
    }

    /**
     * @return int
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
        return sprintf(
            '%s%s-%s',
            $this->getInvoicePrefix(),
            $this->getInvoiceYear(),
            str_pad($this->getInvoiceIncrement(), 4, "0", STR_PAD_LEFT)
        );
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
