<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

/**
 * "Commande"
 */
class Order
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var bool
     */
    private $vatApplicable;

    /**
     * @var string
     */
    private $vatMode;

    /**
     * @var float
     */
    private $vatRate;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Order\Row[]
     */
    private $rows = [];

    /**
     * @var Order\BillingInfo
     */
    private $billingInfo;

    /**
     * @param Sheet             $sheet
     * @param bool              $vatApplicable
     * @param Order\BillingInfo $billingInfo
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        $vatApplicable,
        Order\BillingInfo $billingInfo,
        DateTimeInterface $createdAt
    ) {
        $this->sheet         = $sheet;
        $this->createdAt     = $createdAt;
        $this->vatApplicable = $vatApplicable;
        $this->billingInfo   = $billingInfo;
        $this->vatMode       = $sheet->getEvent()->getMode();
        $this->currency      = $sheet->getEvent()->getCurrency();
        $this->vatRate       = $sheet->getEvent()->getVat();
        $this->rows          = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Get vat
     *
     * @return float
     *
     * @deprecated Use getVatRate instead
     */
    public function getVat()
    {
        return $this->getVatRate();
    }

    /**
     * Get vatMode
     *
     * @return string
     */
    public function getVatMode()
    {
        return $this->vatMode;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get vatRate
     *
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @return Order\BillingInfo
     */
    public function getBillingInfo()
    {
        return $this->billingInfo;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return boolean
     */
    public function isVatApplicable()
    {
        return $this->vatApplicable;
    }

    /**
     * @return Order\Row[]
     */
    public function getRows()
    {
        return $this->rows;
    }
}
