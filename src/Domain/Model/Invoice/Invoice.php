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
    private $number;

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
     * @param Event     $event
     * @param Sheet     $sheet
     * @param string    $number
     * @param float     $total
     * @param float     $totalWithVat
     * @param float     $vatAmount
     * @param \DateTime $createdAt
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        $number,
        $total,
        $totalWithVat,
        $vatAmount,
        \DateTime $createdAt)
    {
        $this->event            = $event;
        $this->sheet            = $sheet;
        $this->number           = $number;
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
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
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
}
