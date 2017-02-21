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
     * @param \DateTime $createdAt
     */
    public function __construct(Event $event, Sheet $sheet, $number, $total, \DateTime $createdAt)
    {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->number = $number;
        $this->total = $total;
        $this->createdAt = $createdAt;
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
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @param Sheet $sheet
     */
    public function setSheet($sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
